<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\MessageKind;
use ZxArt\Assistant\Chat\MessageRole;
use ZxArt\Assistant\Llm\LlmMessageDto;
use ZxArt\Assistant\Llm\LlmRole;
use ZxArt\Assistant\Llm\LlmToolCallDto;

/**
 * What the model is told of a stored conversation: the system prompt, then each
 * message the way the model wrote or read it. Images a tool showed are not kept
 * and are not shown again.
 */
final readonly class ConversationHistory
{
    private const string SYSTEM_PROMPT = <<<'PROMPT'
        You are the assistant of the editors of ZX-Art (zxart.ee), the catalogue of ZX Spectrum software, music, graphics, their authors and groups.
        You help an editor find and fix catalogue data: productions (prods), releases, authors, author aliases, groups and group aliases.

        - Answer in the language the user writes in.
        - Look things up with the tools before you answer; do not guess ids, titles or facts.
        - When a request is ambiguous (several matching entities, unclear intent), ask the user instead of choosing.
        - You never change the site directly. To change anything, call propose_plan with every write action in order;
          the user sees a preview of each action and approves or rejects the plan. After proposing, stop and wait.
        - An action of a plan may use an id that an earlier action of the same plan creates: write {"$ref": N} in place
          of the id, where N is the number of that earlier action, counting from 1.
        - Mention an entity of the site as [[type:id]], for example [[prod:123]] or [[author:45]]; the user sees its title
          as a link. Types: prod, release, author, authorAlias, group, groupAlias.
        - Be brief.
        PROMPT;

    /**
     * @param list<MessageDto> $messages in `seq` order
     * @return list<LlmMessageDto>
     */
    public function build(array $messages): array
    {
        $history = [new LlmMessageDto(LlmRole::System, self::SYSTEM_PROMPT)];
        foreach ($messages as $message) {
            array_push($history, ...$this->told($message));
        }

        return $history;
    }

    /**
     * @return list<LlmMessageDto>
     */
    private function told(MessageDto $message): array
    {
        $payload = $message->payload ?? [];

        return match ($message->kind) {
            MessageKind::Text => [new LlmMessageDto(
                $message->role === MessageRole::User ? LlmRole::User : LlmRole::Assistant,
                $message->text . $this->pageNote($payload),
            )],
            MessageKind::ToolCall => $this->call(
                (string)($payload['callId'] ?? ''),
                (string)($payload['tool'] ?? ''),
                (string)($payload['arguments'] ?? ''),
                (string)($payload['result'] ?? ''),
            ),
            MessageKind::Plan => $this->call(
                (string)($payload['callId'] ?? ''),
                PlanProposer::TOOL_NAME,
                (string)($payload['arguments'] ?? ''),
                'Plan ' . (int)($payload['planId'] ?? 0) . ' is proposed and waits for the user to approve it.',
            ),
            MessageKind::PlanProgress => [new LlmMessageDto(LlmRole::System, $message->text)],
            MessageKind::Error => [],
        };
    }

    /**
     * @return list<LlmMessageDto>
     */
    private function call(string $callId, string $tool, string $arguments, string $result): array
    {
        return [
            new LlmMessageDto(LlmRole::Assistant, '', [new LlmToolCallDto($callId, $tool, $arguments)]),
            new LlmMessageDto(LlmRole::Tool, $result, toolCallId: $callId),
        ];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function pageNote(array $payload): string
    {
        $page = $payload['page'] ?? null;
        if (!is_array($page) || !isset($page['url'])) {
            return '';
        }
        $note = "\n\n(The user is on the page " . (string)$page['url'];
        if (isset($page['entityType'], $page['entityId'])) {
            $note .= ', which shows [[' . (string)$page['entityType'] . ':' . (int)$page['entityId'] . ']]';
        }

        return $note . '.)';
    }
}
