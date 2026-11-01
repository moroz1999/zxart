<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The entities a conversation mentions: `[[type:id]]` in the text of its
 * messages, and the targets and values of its plans' previews.
 */
final readonly class Mentions
{
    /**
     * @param list<MessageDto> $messages
     * @param list<PlanDto> $plans
     * @return list<EntityMentionDto>
     */
    public function find(array $messages, array $plans): array
    {
        $found = [];
        foreach ($messages as $message) {
            preg_match_all('/\[\[(\w+):(\d+)]]/', $message->text, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $this->add($found, $match[1], (int)$match[2]);
            }
        }
        foreach ($plans as $plan) {
            foreach ($plan->actions as $action) {
                $this->walk($found, $action['preview'] ?? null);
            }
        }

        return array_values($found);
    }

    /**
     * @param array<string, EntityMentionDto> $found
     */
    private function walk(array &$found, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }
        if (isset($value['type'], $value['id']) && is_string($value['type']) && is_int($value['id'])) {
            $this->add($found, $value['type'], $value['id']);
        }
        foreach ($value as $item) {
            $this->walk($found, $item);
        }
    }

    /**
     * @param array<string, EntityMentionDto> $found
     */
    private function add(array &$found, string $type, int $id): void
    {
        $entityType = EntityType::tryFrom($type);
        if ($entityType !== null) {
            $mention = new EntityMentionDto($entityType, $id);
            $found[$mention->key()] = $mention;
        }
    }
}
