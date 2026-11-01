<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Chat;

use controller;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\Dto\ConversationDto;
use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Assistant\Chat\Dto\RunDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Chat\PlanStatus;
use ZxArt\Assistant\Chat\RunStatus;
use ZxArt\Assistant\Chat\RunType;

/**
 * The assistant's conversations as the tables keep them; each test is rolled
 * back.
 */
final class ChatDbalTest extends TestCase
{
    private const int USER_ID = 7;
    private const int NOW = 1_800_000_000;

    private Connection $db;
    private ConversationsDbal $conversations;
    private MessagesDbal $messages;
    private RunsDbal $runs;
    private PlansDbal $plans;

    #[\Override]
    protected function setUp(): void
    {
        $container = controller::getInstance()->getContainer();
        $this->db = $container->get(Connection::class);
        $this->conversations = $container->get(ConversationsDbal::class);
        $this->messages = $container->get(MessagesDbal::class);
        $this->runs = $container->get(RunsDbal::class);
        $this->plans = $container->get(PlansDbal::class);
        $this->db->beginTransaction();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testACreatedConversationIsFoundEmpty(): void
    {
        $id = $this->conversations->create(self::USER_ID, self::NOW);

        self::assertEquals(
            new ConversationDto(id: $id, userId: self::USER_ID, title: '', createdAt: self::NOW, lastMessageAt: self::NOW),
            $this->conversations->find($id),
        );
    }

    public function testTouchingAConversationMovesItsLastMessageTime(): void
    {
        $id = $this->conversations->create(self::USER_ID, self::NOW);

        $this->conversations->touch($id, self::NOW + 5);

        self::assertSame(self::NOW + 5, $this->conversations->find($id)?->lastMessageAt);
    }

    public function testAMissingConversationIsNotFound(): void
    {
        self::assertNull($this->conversations->find(999_999_999));
    }

    public function testAUsersConversationsAreListedLatestFirst(): void
    {
        $older = $this->conversations->create(self::USER_ID, self::NOW);
        $newer = $this->conversations->create(self::USER_ID, self::NOW + 10);
        $this->conversations->create(self::USER_ID + 1, self::NOW + 20);

        self::assertSame([$newer, $older], array_map(
            static fn(ConversationDto $conversation): int => $conversation->id,
            $this->conversations->findByUser(self::USER_ID),
        ));
    }

    public function testTheConversationsWithARunningRunAreFound(): void
    {
        $idle = $this->conversations->create(self::USER_ID, self::NOW);
        $busy = $this->conversations->create(self::USER_ID, self::NOW);
        $this->runs->start($busy, RunType::Chat, self::NOW);
        $this->runs->finish($this->runs->start($idle, RunType::Chat, self::NOW), RunStatus::Done, null, self::NOW);

        self::assertSame([$busy], $this->runs->findRunningConversationIds([$idle, $busy]));
        self::assertSame([], $this->runs->findRunningConversationIds([]));
    }

    public function testAStopRequestIsKeptOnTheRun(): void
    {
        $runId = $this->runs->start($this->conversations->create(self::USER_ID, self::NOW), RunType::Chat, self::NOW);

        $this->runs->requestStop($runId);

        self::assertTrue($this->runs->find($runId)?->stopRequested);
    }

    public function testMessagesAreNumberedInTheOrderTheyAreAppended(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);

        $first = $this->messages->append($conversationId, MessageRole::User, MessageKind::Text, 'Привет', null, self::NOW);
        $second = $this->messages->append($conversationId, MessageRole::Assistant, MessageKind::ToolCall, 'search', ['arguments' => ['query' => 'Elite']], self::NOW + 1);

        self::assertEquals(new MessageDto(
            id: $first->id,
            conversationId: $conversationId,
            seq: 1,
            role: MessageRole::User,
            kind: MessageKind::Text,
            text: 'Привет',
            payload: null,
            createdAt: self::NOW,
        ), $first);
        self::assertSame(2, $second->seq);
        self::assertEquals([$first, $second], $this->messages->findAfter($conversationId, 0));
    }

    public function testMessagesAfterASeqAreTheNewerOnes(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $this->messages->append($conversationId, MessageRole::User, MessageKind::Text, 'one', null, self::NOW);
        $second = $this->messages->append($conversationId, MessageRole::Assistant, MessageKind::Text, 'two', null, self::NOW);

        self::assertEquals([$second], $this->messages->findAfter($conversationId, 1));
        self::assertSame([], $this->messages->findAfter($conversationId, 2));
    }

    public function testEachConversationNumbersItsOwnMessages(): void
    {
        $first = $this->conversations->create(self::USER_ID, self::NOW);
        $second = $this->conversations->create(self::USER_ID, self::NOW);
        $this->messages->append($first, MessageRole::User, MessageKind::Text, 'one', null, self::NOW);

        self::assertSame(1, $this->messages->append($second, MessageRole::User, MessageKind::Text, 'one', null, self::NOW)->seq);
    }

    public function testARunIsActiveUntilItFinishes(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $runId = $this->runs->start($conversationId, RunType::Chat, self::NOW);
        $this->runs->recordStep($runId, 100, 20);
        $this->runs->recordStep($runId, 50, 5);

        self::assertEquals(new RunDto(
            id: $runId,
            conversationId: $conversationId,
            type: RunType::Chat,
            status: RunStatus::Running,
            steps: 2,
            tokensIn: 150,
            tokensOut: 25,
            error: null,
            startedAt: self::NOW,
            finishedAt: null,
            stopRequested: false,
        ), $this->runs->findActive($conversationId));

        $this->runs->finish($runId, RunStatus::Failed, 'model unavailable', self::NOW + 9);

        self::assertNull($this->runs->findActive($conversationId));
        $finished = $this->runs->find($runId);
        self::assertSame(RunStatus::Failed, $finished?->status);
        self::assertSame('model unavailable', $finished->error);
        self::assertSame(self::NOW + 9, $finished->finishedAt);
    }

    public function testAProposedPlanKeepsItsActions(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $actions = [['tool' => 'update_prod', 'arguments' => ['id' => 5, 'year' => 1987]]];

        $planId = $this->plans->create($conversationId, 'Fix the year', $actions, self::NOW);

        self::assertEquals(new PlanDto(
            id: $planId,
            conversationId: $conversationId,
            title: 'Fix the year',
            status: PlanStatus::Proposed,
            actions: $actions,
            createdAt: self::NOW,
            approvedAt: null,
            finishedAt: null,
        ), $this->plans->find($planId));
    }

    public function testAnApprovedPlanRecordsWhenAndHowItFinished(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $planId = $this->plans->create($conversationId, 'Fix', [['tool' => 'update_prod']], self::NOW);

        $this->plans->approve($planId, self::NOW + 1);
        $this->plans->saveActions($planId, [['tool' => 'update_prod', 'status' => 'done']]);
        $this->plans->finish($planId, PlanStatus::Done, self::NOW + 2);

        $plan = $this->plans->find($planId);
        self::assertSame(PlanStatus::Done, $plan?->status);
        self::assertSame(self::NOW + 1, $plan->approvedAt);
        self::assertSame(self::NOW + 2, $plan->finishedAt);
        self::assertSame([['tool' => 'update_prod', 'status' => 'done']], $plan->actions);
    }

    public function testSupersedingLeavesNoProposedPlan(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $planId = $this->plans->create($conversationId, 'Fix', [], self::NOW);

        $this->plans->supersedeProposed($conversationId);

        self::assertSame(PlanStatus::Superseded, $this->plans->find($planId)?->status);
    }

    public function testDeletingAConversationDeletesWhatItHolds(): void
    {
        $conversationId = $this->conversations->create(self::USER_ID, self::NOW);
        $this->messages->append($conversationId, MessageRole::User, MessageKind::Text, 'one', null, self::NOW);
        $runId = $this->runs->start($conversationId, RunType::Chat, self::NOW);
        $planId = $this->plans->create($conversationId, 'Fix', [], self::NOW);

        $this->conversations->delete($conversationId);

        self::assertSame([], $this->messages->findAfter($conversationId, 0));
        self::assertNull($this->runs->find($runId));
        self::assertNull($this->plans->find($planId));
    }
}
