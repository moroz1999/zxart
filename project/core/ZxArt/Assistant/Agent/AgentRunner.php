<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use Exception;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Llm\LlmClient;
use ZxArt\Assistant\Llm\LlmException;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmRole;
use ZxArt\Assistant\Llm\LlmToolCallDto;
use ZxArt\Assistant\Llm\LlmToolDto;
use ZxArt\Assistant\Tools\JsonSchemaGenerator;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Shared\Images\ImageDto;

/**
 * One chat run: asks the model, calls the read tools it asks for and feeds the
 * results back until it answers in prose or proposes a plan. Every step is
 * stored as a message first, so the panel shows the run as it goes.
 *
 * Write tools are not offered; the model reaches them only through
 * `propose_plan`. A failed tool call is told to the model, which may try again.
 * A stop the user asked for is honoured before the next step.
 */
final readonly class AgentRunner
{
    public function __construct(
        private LlmClient $llm,
        private ToolRegistry $tools,
        private ToolRequestBuilder $requestBuilder,
        private SerializerInterface $serializer,
        private ConversationHistory $history,
        private PlanProposer $planProposer,
        private ConversationsDbal $conversations,
        private MessagesDbal $messages,
        private RunsDbal $runs,
        private JsonSchemaGenerator $schemaGenerator = new JsonSchemaGenerator(),
        private int $maxSteps = 20,
        private int $timeLimitSeconds = 600,
    ) {
    }

    /**
     * @throws Throwable what nobody expected; the run is marked failed first
     */
    public function run(int $runId): void
    {
        $run = $this->runs->find($runId) ?? throw new RuntimeException("There is no run $runId");
        $conversationId = $run->conversationId;
        try {
            $status = $this->loop($runId, $conversationId);
            $this->runs->finish($runId, $status, null, time());
        } catch (LlmException $exception) {
            $this->fail($runId, $conversationId, $exception->getMessage());
        } catch (Throwable $throwable) {
            $this->fail($runId, $conversationId, 'The assistant failed: ' . $throwable->getMessage());
            throw $throwable;
        }
    }

    /**
     * @throws LlmException
     */
    private function loop(int $runId, int $conversationId): RunStatus
    {
        $startedAt = time();
        $context = $this->history->build($this->messages->findAfter($conversationId, 0));
        $tools = $this->offeredTools();
        for ($step = 1; $step <= $this->maxSteps; $step++) {
            if ($this->runs->find($runId)?->stopRequested === true) {
                $this->store($conversationId, MessageKind::Stopped, 'Stopped by the user', null);
                return RunStatus::Stopped;
            }
            if (time() - $startedAt > $this->timeLimitSeconds) {
                throw new LlmException('The assistant took longer than ' . intdiv($this->timeLimitSeconds, 60) . ' minutes and was stopped');
            }
            $reply = $this->llm->complete($context, $tools, $runId);
            $this->runs->recordStep($runId, $reply->tokensIn, $reply->tokensOut);
            if ($reply->text !== '') {
                $this->store($conversationId, MessageKind::Text, $reply->text, null);
            }
            if ($reply->toolCalls === []) {
                return RunStatus::Done;
            }
            $context[] = new LlmMessageDto(LlmRole::Assistant, $reply->text, $reply->toolCalls);
            $images = [];
            $planProposed = false;
            foreach ($reply->toolCalls as $call) {
                if ($call->name === PlanProposer::TOOL_NAME) {
                    [$planProposedNow, $told] = $this->proposePlan($conversationId, $call);
                    $planProposed = $planProposed || $planProposedNow;
                } else {
                    $told = $this->callTool($conversationId, $call, $images);
                }
                $context[] = new LlmMessageDto(LlmRole::Tool, $told, toolCallId: $call->id);
            }
            if ($planProposed) {
                return RunStatus::WaitingApproval;
            }
            if ($images !== []) {
                $context[] = new LlmMessageDto(LlmRole::User, 'The images the tools returned.', images: $images);
            }
        }

        throw new LlmException("The assistant took more than {$this->maxSteps} steps and was stopped");
    }

    /**
     * @return array{bool, string} whether the plan is proposed, and what the model is told
     */
    private function proposePlan(int $conversationId, LlmToolCallDto $call): array
    {
        try {
            $plan = $this->planProposer->propose($conversationId, $call->id, $call->arguments);
            return [true, PlanProposer::proposedNote($plan->id)];
        } catch (PlanRefusedException $exception) {
            $told = 'Error: ' . $exception->getMessage();
            $this->storeCall($conversationId, $call, $told, true);
            return [false, $told];
        }
    }

    /**
     * @param list<ImageDto> $images collects the images the call returns
     * @return string what the model is told
     */
    private function callTool(int $conversationId, LlmToolCallDto $call, array &$images): string
    {
        try {
            $tool = $this->tools->get($call->name);
            if (!$tool instanceof ReadTool) {
                throw new PlanRefusedException("{$call->name} changes the site; propose it in a plan");
            }
            $result = $tool->execute($this->requestBuilder->build($tool->requestClass(), $call->arguments));
        } catch (Exception $exception) {
            $told = 'Error: ' . $exception->getMessage();
            $this->storeCall($conversationId, $call, $told, true);
            return $told;
        }
        if ($result instanceof ImageDto) {
            $images[] = $result;
            $told = 'The image is shown in the next message.';
        } else {
            $told = $this->serializer->serialize($result, 'json', [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE]);
        }
        $this->storeCall($conversationId, $call, $told, false);

        return $told;
    }

    /**
     * @return list<LlmToolDto>
     */
    private function offeredTools(): array
    {
        $offered = [];
        foreach ($this->tools->all() as $tool) {
            if ($tool instanceof ReadTool) {
                $offered[] = new LlmToolDto($tool->name(), $tool->description(), $this->schemaGenerator->forClass($tool->requestClass()));
            }
        }
        $offered[] = $this->planProposer->tool();

        return $offered;
    }

    private function storeCall(int $conversationId, LlmToolCallDto $call, string $result, bool $failed): void
    {
        $this->store($conversationId, MessageKind::ToolCall, $call->name, [
            'callId' => $call->id,
            'tool' => $call->name,
            'arguments' => $call->arguments,
            'result' => $result,
            'failed' => $failed,
        ]);
    }

    /**
     * @param array<array-key, mixed>|null $payload
     */
    private function store(int $conversationId, MessageKind $kind, string $text, ?array $payload): void
    {
        $now = time();
        $this->messages->append($conversationId, MessageRole::Assistant, $kind, $text, $payload, $now);
        $this->conversations->touch($conversationId, $now);
    }

    private function fail(int $runId, int $conversationId, string $error): void
    {
        $this->store($conversationId, MessageKind::Error, $error, null);
        $this->runs->finish($runId, RunStatus::Failed, $error, time());
    }
}
