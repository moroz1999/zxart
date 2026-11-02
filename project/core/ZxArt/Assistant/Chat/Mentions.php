<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * The entities a conversation mentions: `[[type:id]]` in the text of its
 * messages, the targets and values of its plans' previews, what their done
 * actions created — the type of the preview's target, the id of the result —
 * and, for an action without a preview, the ids its arguments give in fields
 * named `<type>Id` or `<type>Ids` after an entity type (`prodId`, `groupIds`).
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
                $preview = $action['preview'] ?? null;
                $this->walk($found, $preview);
                if ($preview === null && is_string($action['arguments'] ?? null)) {
                    $this->walkArguments($found, json_decode($action['arguments'], true));
                }
                $type = is_array($preview) && is_array($preview['target'] ?? null) ? ($preview['target']['type'] ?? null) : null;
                $id = is_array($action['result'] ?? null) ? ($action['result']['id'] ?? null) : null;
                if (is_string($type) && is_int($id)) {
                    $this->add($found, $type, $id);
                }
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
    private function walkArguments(array &$found, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $key => $item) {
            $type = is_string($key) && preg_match('/^(\w+?)Ids?$/', $key, $match) === 1 ? $match[1] : null;
            if ($type !== null) {
                foreach (is_array($item) ? $item : [$item] as $id) {
                    if (is_int($id)) {
                        $this->add($found, $type, $id);
                    }
                }
            }
            $this->walkArguments($found, $item);
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
