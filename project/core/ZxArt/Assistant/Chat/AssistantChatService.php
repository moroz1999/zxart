<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

use JsonException;
use Throwable;
use ZxArt\Assistant\Agent\AgentRunner;
use ZxArt\Assistant\Agent\PlanExecutor;
use ZxArt\Assistant\Chat\Dbal\ConversationsDbal;
use ZxArt\Assistant\Chat\Dbal\MessagesDbal;
use ZxArt\Assistant\Chat\Dbal\PlansDbal;
use ZxArt\Assistant\Chat\Dbal\RunsDbal;
use ZxArt\Assistant\Chat\Dto\ConversationDto;
use ZxArt\Assistant\Chat\Dto\ConversationStateDto;
use ZxArt\Assistant\Chat\Dto\ConversationSummaryDto;
use ZxArt\Assistant\Chat\Dto\PageDto;
use ZxArt\Assistant\Chat\Dto\RunDto;
use ZxArt\Shared\References\EntityMentionLookup;

/**
 * A user's conversations with the assistant. Another user's conversation does
 * not exist for them. One run at a time per conversation: what the user sends
 * or approves starts it, and the caller runs it once the response is out.
 */
final readonly class AssistantChatService
{
    /** A run that has not finished by then is taken for dead — its process died. */
    private const int RUN_TIMEOUT_SECONDS = 900;
    private const int TITLE_LENGTH = 80;

    public function __construct(
        private ConversationsDbal $conversations,
        private MessagesDbal $messages,
        private RunsDbal $runs,
        private PlansDbal $plans,
        private Mentions $mentions,
        private EntityMentionLookup $mentionLookup,
        private AgentRunner $agentRunner,
        private PlanExecutor $planExecutor,
    ) {
    }

    public function create(int $userId): ConversationDto
    {
        return $this->conversation($userId, $this->conversations->create($userId, time()));
    }

    /**
     * @return list<ConversationSummaryDto> latest activity first
     */
    public function list(int $userId): array
    {
        $conversations = $this->conversations->findByUser($userId);
        $running = $this->runs->findRunningConversationIds(array_map(static fn(ConversationDto $conversation): int => $conversation->id, $conversations));

        return array_map(
            static fn(ConversationDto $conversation): ConversationSummaryDto => new ConversationSummaryDto(
                $conversation->id,
                $conversation->title,
                $conversation->lastMessageAt,
                in_array($conversation->id, $running, true),
            ),
            $conversations,
        );
    }

    /**
     * @throws AssistantException
     */
    public function delete(int $userId, int $conversationId): void
    {
        $this->conversation($userId, $conversationId);
        $this->assertNoActiveRun($conversationId);
        $this->conversations->delete($conversationId);
    }

    /**
     * Asks the active run to stop before its next step; nothing to do without one.
     *
     * @throws AssistantException
     */
    public function stop(int $userId, int $conversationId): void
    {
        $this->conversation($userId, $conversationId);
        $run = $this->activeRun($conversationId);
        if ($run !== null) {
            $this->runs->requestStop($run->id);
        }
    }

    /**
     * @throws AssistantException 404
     */
    public function conversation(int $userId, int $conversationId): ConversationDto
    {
        $conversation = $this->conversations->find($conversationId);
        if ($conversation === null || $conversation->userId !== $userId) {
            throw new AssistantException('Conversation not found', 404);
        }

        return $conversation;
    }

    /**
     * @throws AssistantException
     * @throws JsonException
     */
    public function state(int $userId, int $conversationId, int $afterSeq): ConversationStateDto
    {
        $this->conversation($userId, $conversationId);
        $messages = $this->messages->findAfter($conversationId, $afterSeq);
        $plans = $this->plans->findByConversation($conversationId);

        return new ConversationStateDto(
            conversationId: $conversationId,
            messages: $messages,
            plans: $plans,
            activeRun: $this->activeRun($conversationId),
            refs: $this->mentionLookup->lookup($this->mentions->find($messages, $plans)),
        );
    }

    /**
     * Stores the message and starts the run that answers it; an unapproved
     * plan is dropped.
     *
     * @return int the run, for {@see self::continueChat()}
     * @throws AssistantException
     * @throws JsonException
     */
    public function send(int $userId, int $conversationId, string $text, ?PageDto $page): int
    {
        $conversation = $this->conversation($userId, $conversationId);
        $text = trim($text);
        if ($text === '') {
            throw new AssistantException('The message is empty', 400);
        }
        $this->assertNoActiveRun($conversationId);
        $now = time();
        $this->plans->supersedeProposed($conversationId);
        $payload = $page === null ? null : ['page' => ['url' => $page->url, 'entityType' => $page->entityType, 'entityId' => $page->entityId]];
        $this->messages->append($conversationId, MessageRole::User, MessageKind::Text, $text, $payload, $now);
        $this->conversations->touch($conversationId, $now);
        if ($conversation->title === '') {
            $this->conversations->setTitle($conversationId, mb_substr($text, 0, self::TITLE_LENGTH));
        }

        return $this->runs->start($conversationId, RunType::Chat, $now);
    }

    /**
     * @return int the run, for {@see self::continueExecution()}
     * @throws AssistantException
     * @throws JsonException
     */
    public function approvePlan(int $userId, int $planId): int
    {
        $plan = $this->plans->find($planId) ?? throw new AssistantException('Plan not found', 404);
        $this->conversation($userId, $plan->conversationId);
        if ($plan->status !== PlanStatus::Proposed) {
            throw new AssistantException('The plan is not waiting for approval', 409);
        }
        $this->assertNoActiveRun($plan->conversationId);
        $now = time();
        $this->plans->approve($planId, $now);

        return $this->runs->start($plan->conversationId, RunType::Execute, $now);
    }

    /**
     * @throws Throwable
     */
    public function continueChat(int $runId): void
    {
        $this->agentRunner->run($runId);
    }

    /**
     * Executes the plan, then lets the assistant sum up what came of it —
     * unless the user stopped the execution.
     *
     * @throws Throwable
     */
    public function continueExecution(int $runId, int $planId): void
    {
        $plan = $this->plans->find($planId) ?? throw new AssistantException('Plan not found', 404);
        try {
            $planStatus = $this->planExecutor->execute($planId, $runId);
        } catch (Throwable $throwable) {
            $this->runs->finish($runId, RunStatus::Failed, $throwable->getMessage(), time());
            throw $throwable;
        }
        $this->runs->finish($runId, match ($planStatus) {
            PlanStatus::Done => RunStatus::Done,
            PlanStatus::Stopped => RunStatus::Stopped,
            default => RunStatus::Failed,
        }, null, time());
        if ($planStatus !== PlanStatus::Stopped) {
            $this->agentRunner->run($this->runs->start($plan->conversationId, RunType::Chat, time()));
        }
    }

    private function activeRun(int $conversationId): ?RunDto
    {
        $run = $this->runs->findActive($conversationId);
        if ($run !== null && $run->startedAt < time() - self::RUN_TIMEOUT_SECONDS) {
            $this->runs->finish($run->id, RunStatus::Failed, 'The run did not finish in time', time());
            return null;
        }

        return $run;
    }

    /**
     * @throws AssistantException 409
     */
    private function assertNoActiveRun(int $conversationId): void
    {
        if ($this->activeRun($conversationId) !== null) {
            throw new AssistantException('The assistant is still working on this conversation', 409);
        }
    }
}
