<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Chat;

require_once __DIR__ . '/../WriteToolTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Chat\AssistantChatService;
use ZxArt\Assistant\Chat\AssistantException;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\Dto\PageDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Chat\PlanStatus;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Tests\Integration\Assistant\WriteToolTestCase;

/**
 * The conversations of one user: what they send starts a run, what they ask
 * for answers what is new since, and nobody reaches another user's
 * conversation.
 */
#[RunTestsInSeparateProcesses]
final class AssistantChatServiceTest extends WriteToolTestCase
{
    private const int USER_ID = 7;
    private const int OTHER_USER_ID = 8;

    public function testASentMessageIsStoredWithItsPageAndStartsAChatRun(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);

        $runId = $service->send(self::USER_ID, $conversation->id, 'Исправь год', new PageDto('https://zxart.ee/eng/software/elite/', 'prod', 5));

        $state = $service->state(self::USER_ID, $conversation->id, 0);
        self::assertCount(1, $state->messages);
        self::assertSame('Исправь год', $state->messages[0]->text);
        self::assertSame(['page' => ['url' => 'https://zxart.ee/eng/software/elite/', 'entityType' => 'prod', 'entityId' => 5]], $state->messages[0]->payload);
        self::assertSame($runId, $state->activeRun?->id);
        self::assertSame(RunType::Chat, $state->activeRun->type);
        self::assertSame('Исправь год', $service->conversation(self::USER_ID, $conversation->id)->title);
    }

    public function testNothingIsSentWhileARunIsActive(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $service->send(self::USER_ID, $conversation->id, 'one', null);

        $this->assertAssistantRefused(409, fn() => $service->send(self::USER_ID, $conversation->id, 'two', null));
    }

    public function testARunLeftRunningTooLongNoLongerHoldsTheConversation(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $staleRunId = $this->container->get(RunsDbal::class)->start($conversation->id, RunType::Chat, time() - 3600);

        $service->send(self::USER_ID, $conversation->id, 'again', null);

        self::assertSame(RunStatus::Failed, $this->container->get(RunsDbal::class)->find($staleRunId)?->status);
    }

    public function testAnotherUsersConversationDoesNotExist(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::OTHER_USER_ID);

        $this->assertAssistantRefused(404, fn() => $service->state(self::USER_ID, $conversation->id, 0));
        $this->assertAssistantRefused(404, fn() => $service->send(self::USER_ID, $conversation->id, 'hi', null));
    }

    public function testSendingInsteadOfApprovingSupersedesTheProposedPlan(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $planId = $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $service->send(self::USER_ID, $conversation->id, 'Нет, иначе', null);

        self::assertSame(PlanStatus::Superseded, $this->container->get(PlansDbal::class)->find($planId)?->status);
    }

    public function testApprovingAProposedPlanStartsItsExecution(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $planId = $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $runId = $service->approvePlan(self::USER_ID, $planId);

        self::assertSame(PlanStatus::Executing, $this->container->get(PlansDbal::class)->find($planId)?->status);
        self::assertSame(RunType::Execute, $this->container->get(RunsDbal::class)->find($runId)?->type);
        $this->assertAssistantRefused(409, fn() => $service->approvePlan(self::USER_ID, $planId));
    }

    public function testAnotherUsersPlanCannotBeApproved(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::OTHER_USER_ID);
        $planId = $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $this->assertAssistantRefused(404, fn() => $service->approvePlan(self::USER_ID, $planId));
    }

    public function testCancellingAProposedPlanEndsItAndTellsTheAssistant(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $planId = $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $service->cancelPlan(self::USER_ID, $planId);

        self::assertSame(PlanStatus::Cancelled, $this->container->get(PlansDbal::class)->find($planId)?->status);
        $state = $service->state(self::USER_ID, $conversation->id, 0);
        self::assertNull($state->activeRun);
        self::assertCount(1, $state->messages);
        self::assertSame(MessageKind::PlanProgress, $state->messages[0]->kind);
        self::assertSame("Plan $planId cancelled by the user", $state->messages[0]->text);
        self::assertSame(['planId' => $planId], $state->messages[0]->payload);
        $this->assertAssistantRefused(409, fn() => $service->cancelPlan(self::USER_ID, $planId));
    }

    public function testAnotherUsersPlanCannotBeCancelled(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::OTHER_USER_ID);
        $planId = $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $this->assertAssistantRefused(404, fn() => $service->cancelPlan(self::USER_ID, $planId));
    }

    public function testTheStateMentionsWhatAnExecutedPlanCreated(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $this->container->get(PlansDbal::class)->create($conversation->id, 'New game', [[
            'tool' => 'create_prod',
            'arguments' => '{}',
            'preview' => ['kind' => 'create', 'target' => ['type' => 'prod', 'id' => null, 'title' => 'Goldwrite Demo', 'createdInStep' => null], 'changes' => [], 'consequences' => []],
            'dependsOn' => [],
            'status' => 'done',
            'result' => ['id' => $prodId],
            'error' => null,
        ]], time());

        $state = $service->state(self::USER_ID, $conversation->id, 0);

        self::assertSame(['prod:' . $prodId], array_keys($state->refs));
    }

    public function testTheStateMentionsTheEntitiesAnUnpreviewedActionNamesById(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $this->container->get(PlansDbal::class)->create($conversation->id, 'Credit', [[
            'tool' => 'create_release',
            'arguments' => json_encode(['fields' => ['title' => 'Tape', 'prodId' => $prodId, 'publisherIds' => [5], 'authorIds' => [['$ref' => 1]]]], JSON_THROW_ON_ERROR),
            'preview' => null,
            'dependsOn' => [1],
            'status' => 'pending',
            'result' => null,
            'error' => null,
        ]], time());

        $state = $service->state(self::USER_ID, $conversation->id, 0);

        self::assertSame(['prod:' . $prodId], array_keys($state->refs));
    }

    public function testTheStateIsWhatIsNewAndTheEntitiesItMentions(): void
    {
        $this->signInAsEditor();
        $prodId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldwrite Demo'))->id;
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $messages = $this->container->get(MessagesDbal::class);
        $messages->append($conversation->id, MessageRole::User, MessageKind::Text, 'one', null, time());
        $messages->append($conversation->id, MessageRole::Assistant, MessageKind::Text, "Это [[prod:$prodId]] и [[prod:999999999]]", null, time());
        $this->container->get(PlansDbal::class)->create($conversation->id, 'Fix', [], time());

        $state = $service->state(self::USER_ID, $conversation->id, 1);

        self::assertCount(1, $state->messages);
        self::assertSame(2, $state->messages[0]->seq);
        self::assertSame(['prod:' . $prodId], array_keys($state->refs));
        self::assertSame('Goldwrite Demo', $state->refs['prod:' . $prodId]->title);
        self::assertCount(1, $state->plans);
        self::assertNull($state->activeRun);
    }

    public function testTheUsersConversationsAreListedWithTheRunningOnesMarked(): void
    {
        $service = $this->service();
        $idle = $service->create(self::USER_ID);
        $busy = $service->create(self::USER_ID);
        $service->send(self::USER_ID, $busy->id, 'Найди Elite', null);
        $service->create(self::OTHER_USER_ID);

        $listed = $service->list(self::USER_ID);

        self::assertSame([$busy->id, $idle->id], array_map(static fn($item): int => $item->id, $listed));
        self::assertSame('Найди Elite', $listed[0]->title);
        self::assertTrue($listed[0]->running);
        self::assertFalse($listed[1]->running);
    }

    public function testADeletedConversationIsGone(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);

        $service->delete(self::USER_ID, $conversation->id);

        $this->assertAssistantRefused(404, fn() => $service->state(self::USER_ID, $conversation->id, 0));
    }

    public function testAConversationIsNotDeletedWhileARunWorksOnIt(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $service->send(self::USER_ID, $conversation->id, 'one', null);

        $this->assertAssistantRefused(409, fn() => $service->delete(self::USER_ID, $conversation->id));
    }

    public function testAnotherUsersConversationCannotBeDeletedOrStopped(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::OTHER_USER_ID);

        $this->assertAssistantRefused(404, fn() => $service->delete(self::USER_ID, $conversation->id));
        $this->assertAssistantRefused(404, fn() => $service->stop(self::USER_ID, $conversation->id));
    }

    public function testStoppingAsksTheActiveRunToStop(): void
    {
        $service = $this->service();
        $conversation = $service->create(self::USER_ID);
        $runId = $service->send(self::USER_ID, $conversation->id, 'one', null);

        $service->stop(self::USER_ID, $conversation->id);

        self::assertTrue($this->container->get(RunsDbal::class)->find($runId)?->stopRequested);
    }

    private function service(): AssistantChatService
    {
        return $this->container->get(AssistantChatService::class);
    }

    private function assertAssistantRefused(int $statusCode, callable $call): void
    {
        try {
            $call();
            self::fail('The call must be refused');
        } catch (AssistantException $exception) {
            self::assertSame($statusCode, $exception->getStatusCode());
        }
    }
}
