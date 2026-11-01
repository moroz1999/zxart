<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Agent;

require_once __DIR__ . '/../WriteToolTestCase.php';
require_once __DIR__ . '/AgentFakes.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use stdClass;
use ZxArt\Assistant\Agent\PlanExecutor;
use ZxArt\Assistant\Agent\PlanProposer;
use ZxArt\Assistant\Agent\PlanRefusedException;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\AssistantChatService;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;
use ZxArt\Assistant\Llm\LlmClient;
use ZxArt\Assistant\Llm\LlmReplyDto;
use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\PlanStatus;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Tests\Integration\Assistant\WriteToolTestCase;

/**
 * A plan is checked and previewed when the assistant proposes it, and runs its
 * actions in order once the user approves it; an action may take the id an
 * earlier one created.
 */
#[RunTestsInSeparateProcesses]
final class PlansTest extends WriteToolTestCase
{
    private const int NOW = 1_800_000_000;

    private int $conversationId;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->conversationId = $this->container->get(ConversationsDbal::class)->create(7, self::NOW);
    }

    public function testTheProposeToolListsTheWriteToolsAndSendsForTheirArguments(): void
    {
        $description = $this->container->get(PlanProposer::class)->tool()->description;

        self::assertStringContainsString('update_prod', $description);
        self::assertStringNotContainsString('Changes the fields', $description);
        self::assertStringContainsString('describe_write_tool', $description);
        self::assertStringNotContainsString('"fields":{"type":"object"', $description);
    }

    public function testAProposedPlanKeepsThePreviewOfEachActionItCanPreview(): void
    {
        $this->signInAsEditor();
        $prodId = $this->createProd();

        $plan = $this->propose([
            'title' => 'Release and fix',
            'actions' => [
                ['tool' => 'update_prod', 'arguments' => ['id' => $prodId, 'fields' => ['year' => 1987]]],
                ['tool' => 'create_release', 'arguments' => ['fields' => ['title' => 'Tape', 'prodId' => $prodId]]],
            ],
        ]);

        self::assertSame(PlanStatus::Proposed, $plan->status);
        self::assertSame('Release and fix', $plan->title);
        self::assertSame('update_prod', $plan->actions[0]['tool']);
        self::assertSame('update', $plan->actions[0]['preview']['kind']);
        self::assertSame([['field' => 'year', 'before' => 0, 'after' => 1987]], $plan->actions[0]['preview']['changes']);
        self::assertSame('pending', $plan->actions[0]['status']);
        self::assertSame('create', $plan->actions[1]['preview']['kind']);

        $message = end($this->messages());
        self::assertSame(MessageKind::Plan, $message->kind);
        self::assertSame('Release and fix', $message->text);
        self::assertSame(['callId' => 'call_1', 'arguments' => $this->arguments, 'planId' => $plan->id], $message->payload);
    }

    public function testAnActionThatTakesAnEarlierResultIsNotPreviewed(): void
    {
        $this->signInAsEditor();

        $plan = $this->propose([
            'title' => 'New game',
            'actions' => [
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo']]],
                ['tool' => 'create_release', 'arguments' => ['fields' => ['title' => 'Tape', 'prodId' => ['$ref' => 1]]]],
            ],
        ]);

        self::assertNull($plan->actions[1]['preview']);
        self::assertSame([1], $plan->actions[1]['dependsOn']);
    }

    public function testAPlanIsRefusedForAToolItCannotRun(): void
    {
        $this->signInAsEditor();

        $this->assertPlanRefused('Action 1: there is no tool named make_prod', ['title' => 'x', 'actions' => [['tool' => 'make_prod', 'arguments' => new stdClass()]]]);
        $this->assertPlanRefused('Action 1: search does not change the site; call it directly', ['title' => 'x', 'actions' => [['tool' => 'search', 'arguments' => ['query' => 'a']]]]);
        $this->assertPlanRefused('Action 1 refers to action 2, which does not come before it', [
            'title' => 'x',
            'actions' => [
                ['tool' => 'create_release', 'arguments' => ['fields' => ['title' => 'Tape', 'prodId' => ['$ref' => 2]]]],
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Demo']]],
            ],
        ]);
        $this->assertPlanRefused('The plan has no actions', ['title' => 'x', 'actions' => []]);
        self::assertSame([], $this->messages());
    }

    public function testAPlanIsRefusedWhenAPreviewRefusesTheAction(): void
    {
        $this->signInAsEditor();

        $this->assertPlanRefused('Action 1 (create_release): ', ['title' => 'x', 'actions' => [['tool' => 'create_release', 'arguments' => ['fields' => ['title' => 'Tape']]]]]);
    }

    public function testAnApprovedPlanRunsItsActionsAndPassesCreatedIdsOn(): void
    {
        $this->signInAsEditor();
        $plan = $this->propose([
            'title' => 'New game',
            'actions' => [
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo']]],
                ['tool' => 'create_release', 'arguments' => ['fields' => ['title' => 'Tape', 'prodId' => ['$ref' => 1]]]],
            ],
        ]);

        $this->container->get(PlanExecutor::class)->execute($plan->id, $this->executeRun());

        $executed = $this->container->get(PlansDbal::class)->find($plan->id);
        self::assertSame(PlanStatus::Done, $executed?->status);
        self::assertSame('done', $executed->actions[0]['status']);
        $prodId = $executed->actions[0]['result']['id'];
        $releaseId = $executed->actions[1]['result']['id'];
        self::assertSame($prodId, $this->container->get(ReleaseStateService::class)->get($releaseId)->fields->prodId);
        $progress = array_values(array_filter($this->messages(), static fn(MessageDto $message): bool => $message->kind === MessageKind::PlanProgress));
        self::assertSame([
            "Plan {$plan->id}, action 1 (create_prod): done. {\"id\":$prodId}",
            "Plan {$plan->id}, action 2 (create_release): done. {\"id\":$releaseId}",
        ], array_map(static fn(MessageDto $message): string => $message->text, $progress));
    }

    public function testAFailedActionStopsThePlan(): void
    {
        $this->signInAsEditor();
        $plan = $this->propose([
            'title' => 'Broken',
            'actions' => [
                ['tool' => 'update_prod', 'arguments' => ['id' => 999_999_999, 'fields' => ['year' => 1987]]],
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo']]],
            ],
        ], preview: false);

        $this->container->get(PlanExecutor::class)->execute($plan->id, $this->executeRun());

        $executed = $this->container->get(PlansDbal::class)->find($plan->id);
        self::assertSame(PlanStatus::Failed, $executed?->status);
        self::assertSame('failed', $executed->actions[0]['status']);
        self::assertNotSame('', $executed->actions[0]['error']);
        self::assertSame('skipped', $executed->actions[1]['status']);
        self::assertSame([], $this->newElementIds());
    }

    public function testAStoppedPlanSkipsTheActionsLeft(): void
    {
        $this->signInAsEditor();
        $plan = $this->propose([
            'title' => 'Two games',
            'actions' => [
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo']]],
                ['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo 2']]],
            ],
        ]);
        $runs = $this->container->get(RunsDbal::class);
        $runId = $runs->start($this->conversationId, RunType::Execute, self::NOW);
        $runs->requestStop($runId);

        $this->container->get(PlanExecutor::class)->execute($plan->id, $runId);

        $executed = $this->container->get(PlansDbal::class)->find($plan->id);
        self::assertSame(PlanStatus::Stopped, $executed?->status);
        self::assertSame(['skipped', 'skipped'], array_column($executed->actions, 'status'));
    }

    public function testAnExecutedPlanIsSummedUpByTheAssistant(): void
    {
        $this->signInAsEditor();
        $plan = $this->propose([
            'title' => 'New game',
            'actions' => [['tool' => 'create_prod', 'arguments' => ['fields' => ['title' => 'Goldwrite Demo']]]],
        ]);
        $llm = new ScriptedLlmClient([new LlmReplyDto('Готово', [], 1, 1)]);
        $this->container->set(LlmClient::class, $llm);
        $runs = $this->container->get(RunsDbal::class);
        $runId = $this->container->get(AssistantChatService::class)->approvePlan(7, $plan->id);

        $this->container->get(AssistantChatService::class)->continueExecution($runId, $plan->id);

        self::assertSame(RunStatus::Done, $runs->find($runId)?->status);
        self::assertStringStartsWith("Plan {$plan->id}, action 1 (create_prod): done.", end($llm->askedWith[0])->text);
        $last = end($this->messages());
        self::assertSame('Готово', $last->text);
        self::assertNull($runs->findActive($this->conversationId));
    }

    private string $arguments = '';

    /**
     * @param array<string, mixed> $plan
     * @param bool $preview false stores the plan as given, to run what a preview would refuse
     */
    private function propose(array $plan, bool $preview = true): PlanDto
    {
        $this->arguments = json_encode($plan, JSON_THROW_ON_ERROR);
        if (!$preview) {
            $actions = array_map(
                static fn(array $action): array => [...$action, 'arguments' => json_encode($action['arguments'], JSON_THROW_ON_ERROR), 'preview' => null, 'dependsOn' => [], 'status' => 'pending', 'result' => null, 'error' => null],
                $plan['actions'],
            );
            $planId = $this->container->get(PlansDbal::class)->create($this->conversationId, $plan['title'], $actions, self::NOW);

            return $this->container->get(PlansDbal::class)->find($planId);
        }

        return $this->container->get(PlanProposer::class)->propose($this->conversationId, 'call_1', $this->arguments);
    }

    /**
     * @param array<string, mixed> $plan
     */
    private function assertPlanRefused(string $messageStart, array $plan): void
    {
        try {
            $this->propose($plan);
            self::fail('The plan must be refused');
        } catch (PlanRefusedException $exception) {
            self::assertStringStartsWith($messageStart, $exception->getMessage());
        }
    }

    /**
     * @return list<MessageDto>
     */
    private function messages(): array
    {
        return $this->container->get(MessagesDbal::class)->findAfter($this->conversationId, 0);
    }

    private function executeRun(): int
    {
        return $this->container->get(RunsDbal::class)->start($this->conversationId, RunType::Execute, self::NOW);
    }

    private function createProd(): int
    {
        return $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
    }
}
