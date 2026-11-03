<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Agent;

require_once __DIR__ . '/AgentFakes.php';

use controller;
use DI\Container;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolRegistry;
use ZxArt\Assistant\Agent\AgentRunner;
use ZxArt\Assistant\Agent\ConversationHistory;
use ZxArt\Assistant\Agent\PlanProposer;
use ZxArt\Assistant\Agent\WriteToolCatalogue;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;
use ZxArt\Assistant\Llm\LlmException;
use ZxArt\Assistant\Llm\LlmReplyDto;
use ZxArt\Assistant\Llm\LlmRole;
use ZxArt\Assistant\Llm\LlmToolCallDto;
use ZxArt\Assistant\Llm\LlmToolDto;

/**
 * A run asks the model, calls the read tools it asks for, feeds the results
 * back, and stores every step as a message of the conversation.
 */
final class AgentRunnerTest extends TestCase
{
    private const int NOW = 1_800_000_000;

    private Container $container;
    private Connection $db;
    private int $conversationId;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = controller::getInstance()->getContainer();
        $this->db = $this->container->get(Connection::class);
        $this->db->beginTransaction();
        $this->conversationId = $this->container->get(ConversationsDbal::class)->create(7, self::NOW);
        $this->container->get(MessagesDbal::class)->append($this->conversationId, MessageRole::User, MessageKind::Text, 'Повтори «привет»', null, self::NOW);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testAProseReplyIsStoredAndTheRunIsDone(): void
    {
        $llm = new ScriptedLlmClient([new LlmReplyDto('Готово', [], 100, 10)]);

        $runId = $this->runAgent($llm);

        self::assertSame(['Повтори «привет»', 'Готово'], $this->texts());
        $run = $this->container->get(RunsDbal::class)->find($runId);
        self::assertSame(RunStatus::Done, $run?->status);
        self::assertSame(1, $run->steps);
        self::assertSame(100, $run->tokensIn);
        self::assertSame(['echo', 'describe_write_tool', 'propose_plan'], $llm->offeredTools[0]);
        self::assertSame(LlmRole::System, $llm->askedWith[0][0]->role);
    }

    public function testAToolCallIsAnsweredAndTheModelAskedAgain(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'echo', '{"text":"привет"}')], 100, 10),
            new LlmReplyDto('Привет', [], 120, 5),
        ]);

        $this->runAgent($llm);

        $toolCall = $this->messages()[1];
        self::assertSame(MessageKind::ToolCall, $toolCall->kind);
        self::assertSame('echo', $toolCall->text);
        self::assertSame(
            ['callId' => 'call_1', 'tool' => 'echo', 'arguments' => '{"text":"привет"}', 'result' => '{"echoed":"привет"}', 'failed' => false],
            $toolCall->payload,
        );
        self::assertSame('{"echoed":"привет"}', end($llm->askedWith[1])->text);
        self::assertSame('Привет', $this->messages()[2]->text);
    }

    public function testAWriteToolIsDescribedOnRequest(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'describe_write_tool', '{"name":"update_prod"}')], 100, 10),
            new LlmReplyDto('Понял', [], 100, 10),
        ]);

        $this->runAgent($llm);

        $toolCall = $this->messages()[1];
        self::assertSame('describe_write_tool', $toolCall->text);
        self::assertFalse($toolCall->payload['failed'] ?? null);
        self::assertStringContainsString('"name":"update_prod"', end($llm->askedWith[1])->text);
    }

    public function testAReadActionOfTheAssistantIsCalledWithItsArguments(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'echo', '{"text":"привет"}')], 100, 10),
            new LlmReplyDto('', [new LlmToolCallDto('call_2', 'echo', '{"text":"привет","fields":true}')], 100, 10),
            new LlmReplyDto('Понял', [], 100, 10),
        ]);

        $this->runAgent($llm, new ToolRegistry([new EchoAction()]));

        self::assertSame('{"echoed":"привет"}', end($llm->askedWith[1])->text);
        self::assertSame('Error: Unknown fields: fields', end($llm->askedWith[2])->text);
        self::assertTrue($this->messages()[2]->payload['failed'] ?? null);
    }

    public function testTheReadActionsOfTheAssistantAreOfferedWithTheirSchemasAndItsWriteActionsAreNot(): void
    {
        $llm = new ScriptedLlmClient([new LlmReplyDto('Понял', [], 100, 10)]);

        $this->runAgent($llm, new ToolRegistry([new EchoAction(), new RenameAction()]));

        $offered = array_values(array_filter($llm->offered[0], static fn(LlmToolDto $tool): bool => $tool->name === 'echo'));
        self::assertCount(1, $offered);
        self::assertSame(Arguments::schema(EchoAction::SCHEMA), $offered[0]->parameters);
        self::assertNotContains('rename_thing', $llm->offeredTools[0]);
    }

    public function testAWriteActionOfTheAssistantIsNotCalledOutsideAPlan(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'rename_thing', '{"id":5}')], 100, 10),
            new LlmReplyDto('Понял', [], 100, 10),
        ]);

        $this->runAgent($llm, new ToolRegistry([new RenameAction()]));

        self::assertSame('Error: rename_thing changes the site; propose it in a plan', end($llm->askedWith[1])->text);
    }

    public function testAFailedToolCallIsToldToTheModelAndTheRunGoesOn(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'echo', '{"txt":"привет"}')], 100, 10),
            new LlmReplyDto('', [new LlmToolCallDto('call_2', 'no_such_tool', '{}')], 100, 10),
            new LlmReplyDto('Не вышло', [], 100, 10),
        ]);

        $runId = $this->runAgent($llm);

        self::assertSame('Error: Unknown fields: txt', end($llm->askedWith[1])->text);
        self::assertSame('Error: There is no tool named no_such_tool', end($llm->askedWith[2])->text);
        self::assertTrue($this->messages()[1]->payload['failed'] ?? null);
        self::assertSame(RunStatus::Done, $this->container->get(RunsDbal::class)->find($runId)?->status);
    }

    public function testAWriteToolIsNotCalledOutsideAPlan(): void
    {
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'update_prod', '{"id":5,"fields":{}}')], 100, 10),
            new LlmReplyDto('Понял', [], 100, 10),
        ]);

        $this->runAgent($llm, new ToolRegistry([new EchoAction(), ...$this->container->get(ToolRegistry::class)->all()]));

        self::assertSame('Error: update_prod changes the site; propose it in a plan', end($llm->askedWith[1])->text);
    }

    public function testAModelFailureEndsTheRunWithAnError(): void
    {
        $runId = $this->runAgent(new ScriptedLlmClient([new LlmException('The model is unavailable')]));

        $error = $this->messages()[1];
        self::assertSame(MessageKind::Error, $error->kind);
        self::assertSame('The model is unavailable', $error->text);
        $run = $this->container->get(RunsDbal::class)->find($runId);
        self::assertSame(RunStatus::Failed, $run?->status);
        self::assertSame('The model is unavailable', $run->error);
    }

    public function testARunThatNeverAnswersStopsAtTheStepLimit(): void
    {
        $call = new LlmReplyDto('', [new LlmToolCallDto('call', 'echo', '{"text":"a"}')], 1, 1);

        $runId = $this->runAgent(new ScriptedLlmClient([$call, $call, $call]), maxSteps: 3);

        $run = $this->container->get(RunsDbal::class)->find($runId);
        self::assertSame(RunStatus::Failed, $run?->status);
        self::assertSame(3, $run->steps);
        self::assertSame('The assistant took more than 3 steps and was stopped', end($this->messages())->text);
    }

    public function testARunAskedToStopEndsBeforeItsNextStep(): void
    {
        $runs = $this->container->get(RunsDbal::class);
        $llm = new ScriptedLlmClient([
            new LlmReplyDto('', [new LlmToolCallDto('call_1', 'echo', '{"text":"a"}')], 1, 1),
            new LlmReplyDto('never asked', [], 1, 1),
        ]);
        $llm->afterReply = static function (int $runId) use ($runs): void {
            $runs->requestStop($runId);
        };

        $runId = $this->runAgent($llm);

        self::assertCount(1, $llm->askedWith);
        self::assertSame(RunStatus::Stopped, $runs->find($runId)?->status);
        $last = end($this->messages());
        self::assertSame(MessageKind::Stopped, $last->kind);
    }

    private function runAgent(ScriptedLlmClient $llm, ?ToolRegistry $tools = null, int $maxSteps = 20): int
    {
        $runs = $this->container->get(RunsDbal::class);
        $runId = $runs->start($this->conversationId, RunType::Chat, self::NOW);
        $tools ??= new ToolRegistry([new EchoAction()]);
        $runner = new AgentRunner(
            llm: $llm,
            tools: $tools,
            serializer: $this->container->get(SerializerInterface::class),
            history: $this->container->get(ConversationHistory::class),
            planProposer: $this->container->get(PlanProposer::class),
            writeTools: $this->container->get(WriteToolCatalogue::class),
            conversations: $this->container->get(ConversationsDbal::class),
            messages: $this->container->get(MessagesDbal::class),
            runs: $runs,
            maxSteps: $maxSteps,
        );
        $runner->run($runId);

        return $runId;
    }

    /**
     * @return list<MessageDto>
     */
    private function messages(): array
    {
        return $this->container->get(MessagesDbal::class)->findAfter($this->conversationId, 0);
    }

    /**
     * @return list<string>
     */
    private function texts(): array
    {
        return array_map(static fn(MessageDto $message): string => $message->text, $this->messages());
    }
}
