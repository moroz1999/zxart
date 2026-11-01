<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use Exception;
use JsonException;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Chat\PlanStatus;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Assistant\Tools\WriteTool;

/**
 * Runs an approved plan's actions in order, each through its write tool, and
 * stops at the first that fails, or before the next one once the user asks the
 * run to stop. Every action's outcome is stored on the plan and told in the
 * conversation.
 */
final readonly class PlanExecutor
{
    public function __construct(
        private ToolRegistry $tools,
        private ToolRequestBuilder $requestBuilder,
        private SerializerInterface $serializer,
        private PlanReferences $references,
        private PlansDbal $plans,
        private MessagesDbal $messages,
        private ConversationsDbal $conversations,
        private RunsDbal $runs,
    ) {
    }

    /**
     * @param int $runId the run executing it, which the user may stop
     * @return PlanStatus how the plan ended
     * @throws JsonException
     */
    public function execute(int $planId, int $runId): PlanStatus
    {
        $plan = $this->plans->find($planId) ?? throw new RuntimeException("There is no plan $planId");
        $actions = $plan->actions;
        /** @var array<int, array<array-key, mixed>> $results by action number */
        $results = [];
        $status = PlanStatus::Done;
        foreach ($actions as $index => $action) {
            $number = $index + 1;
            if ($status === PlanStatus::Done && $this->runs->find($runId)?->stopRequested === true) {
                $status = PlanStatus::Stopped;
                $this->tell($plan->conversationId, $planId, $number, "Plan $planId stopped by the user before action $number");
            }
            if ($status !== PlanStatus::Done) {
                $actions[$index]['status'] = PlanActionStatus::Skipped->value;
                continue;
            }
            $toolName = (string)$action['tool'];
            try {
                $result = $this->run($toolName, (string)$action['arguments'], $results);
                $results[$number] = $result;
                $actions[$index]['status'] = PlanActionStatus::Done->value;
                $actions[$index]['result'] = $result;
                $outcome = 'done. ' . json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            } catch (Exception $exception) {
                $status = PlanStatus::Failed;
                $actions[$index]['status'] = PlanActionStatus::Failed->value;
                $actions[$index]['error'] = $exception->getMessage();
                $outcome = 'failed. ' . $exception->getMessage();
            }
            $this->plans->saveActions($planId, $actions);
            $this->tell($plan->conversationId, $planId, $number, "Plan $planId, action $number ($toolName): $outcome");
        }
        $this->plans->saveActions($planId, $actions);
        $this->plans->finish($planId, $status, time());
        $this->conversations->touch($plan->conversationId, time());

        return $status;
    }

    private function tell(int $conversationId, int $planId, int $action, string $text): void
    {
        $this->messages->append(
            $conversationId,
            MessageRole::Assistant,
            MessageKind::PlanProgress,
            $text,
            ['planId' => $planId, 'action' => $action],
            time(),
        );
    }

    /**
     * @param array<int, array<array-key, mixed>> $results
     * @return array<array-key, mixed>
     * @throws Exception
     */
    private function run(string $toolName, string $arguments, array $results): array
    {
        $tool = $this->tools->get($toolName);
        if (!$tool instanceof WriteTool) {
            throw new PlanRefusedException("$toolName does not change the site");
        }
        $resolved = $this->references->resolve(
            json_decode($arguments, false, 512, JSON_THROW_ON_ERROR),
            static function (int $action) use ($results): int {
                $id = $results[$action]['id'] ?? null;
                if (!is_int($id)) {
                    throw new PlanRefusedException("Action $action created no id to refer to");
                }
                return $id;
            },
        );
        $result = $tool->execute($this->requestBuilder->build($tool->requestClass(), json_encode($resolved, JSON_THROW_ON_ERROR)));
        /** @var array<array-key, mixed> */
        return json_decode($this->serializer->serialize($result, 'json', [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE]), true, 512, JSON_THROW_ON_ERROR);
    }
}
