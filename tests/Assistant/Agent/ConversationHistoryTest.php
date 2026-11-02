<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Agent;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Agent\ConversationHistory;
use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmRole;
use ZxArt\Assistant\Llm\LlmToolCallDto;
use ZxArt\Assistant\Skills\SkillCatalogue;

/**
 * What the model is told of a stored conversation.
 */
final class ConversationHistoryTest extends TestCase
{
    public function testTheSystemPromptLeadsAndProseFollowsAsWritten(): void
    {
        $history = $this->history()->build([
            $this->message(MessageRole::User, MessageKind::Text, 'Найди Elite'),
            $this->message(MessageRole::Assistant, MessageKind::Text, 'Нашёл [[prod:5]]'),
        ]);

        self::assertSame(LlmRole::System, $history[0]->role);
        self::assertEquals([
            new LlmMessageDto(LlmRole::User, 'Найди Elite'),
            new LlmMessageDto(LlmRole::Assistant, 'Нашёл [[prod:5]]'),
        ], array_slice($history, 1));
    }

    public function testTheSystemPromptListsTheSkillsToLoad(): void
    {
        $prompt = $this->history()->build([])[0]->text;

        self::assertStringContainsString('load_skill', $prompt);
        self::assertStringContainsString("- import-prod: Importing a production from an external site.\n- merge-duplicates: Joining two entries of one production.", $prompt);
    }

    public function testTheUserPageIsToldAlongWithTheMessage(): void
    {
        $history = $this->history()->build([
            $this->message(MessageRole::User, MessageKind::Text, 'Исправь год', [
                'page' => ['url' => 'https://zxart.ee/eng/software/elite/', 'entityType' => 'prod', 'entityId' => 5],
            ]),
        ]);

        self::assertEquals(
            new LlmMessageDto(LlmRole::User, "Исправь год\n\n(The user is on the page https://zxart.ee/eng/software/elite/, which shows [[prod:5]].)"),
            $history[1],
        );
    }

    public function testAToolCallIsTheAssistantsCallAndItsResult(): void
    {
        $history = $this->history()->build([
            $this->message(MessageRole::Assistant, MessageKind::ToolCall, 'search', [
                'callId' => 'call_1',
                'tool' => 'search',
                'arguments' => '{"query":"Elite"}',
                'result' => '{"found":[]}',
            ]),
        ]);

        self::assertEquals([
            new LlmMessageDto(LlmRole::Assistant, '', [new LlmToolCallDto('call_1', 'search', '{"query":"Elite"}')]),
            new LlmMessageDto(LlmRole::Tool, '{"found":[]}', toolCallId: 'call_1'),
        ], array_slice($history, 1));
    }

    public function testAProposedPlanIsTheCallThatProposedIt(): void
    {
        $history = $this->history()->build([
            $this->message(MessageRole::Assistant, MessageKind::Plan, 'Fix the year', [
                'callId' => 'call_2',
                'arguments' => '{"title":"Fix the year","actions":[]}',
                'planId' => 12,
            ]),
        ]);

        self::assertEquals([
            new LlmMessageDto(LlmRole::Assistant, '', [new LlmToolCallDto('call_2', 'propose_plan', '{"title":"Fix the year","actions":[]}')]),
            new LlmMessageDto(LlmRole::Tool, 'Plan 12 is proposed and waits for the user to approve it.', toolCallId: 'call_2'),
        ], array_slice($history, 1));
    }

    public function testPlanProgressIsToldAsANoteAndErrorsAreLeftOut(): void
    {
        $history = $this->history()->build([
            $this->message(MessageRole::Assistant, MessageKind::PlanProgress, 'Plan 12, action 1 (update_prod): done. {"id":5}'),
            $this->message(MessageRole::Assistant, MessageKind::Error, 'The model is unavailable'),
        ]);

        self::assertEquals([
            new LlmMessageDto(LlmRole::System, 'Plan 12, action 1 (update_prod): done. {"id":5}'),
        ], array_slice($history, 1));
    }

    /**
     * @param array<array-key, mixed>|null $payload
     */
    private function history(): ConversationHistory
    {
        return new ConversationHistory(new SkillCatalogue(__DIR__ . '/../Skills/Fixtures/valid'));
    }

    private function message(MessageRole $role, MessageKind $kind, string $text, ?array $payload = null): MessageDto
    {
        return new MessageDto(1, 1, 1, $role, $kind, $text, $payload, 0);
    }
}
