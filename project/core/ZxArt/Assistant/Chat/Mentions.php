<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

use ZxArt\Assistant\Chat\Dto\MessageDto;
use ZxArt\Assistant\Chat\Dto\PlanDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * The entities a conversation mentions: `[[type:id]]` in the text of its
 * messages and, for each action of its plans, its target and what it created —
 * of the type the action names, the id of its state and of its result — the
 * refs its state holds, and the ids its arguments and its state give in fields
 * named `<name>Id` or `<name>Ids`. The name tells no type (`groupIds` holds
 * group aliases too, `publisherIds` authors as well), so such an id is
 * mentioned as what it is, and left out when it is of nothing.
 *
 * @psalm-api
 */
final readonly class Mentions
{
    public function __construct(
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    /**
     * @param list<MessageDto> $messages
     * @param list<PlanDto> $plans
     * @return list<EntityMentionDto>
     */
    public function find(array $messages, array $plans): array
    {
        $found = [];
        $ids = [];
        foreach ($messages as $message) {
            preg_match_all('/\[\[(\w+):(\d+)]]/', $message->text, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $this->add($found, $match[1], (int)$match[2]);
            }
        }
        foreach ($plans as $plan) {
            foreach ($plan->actions as $action) {
                /** @var mixed $arguments */
                $arguments = $action['arguments'] ?? null;
                if (is_string($arguments)) {
                    $this->walkArguments($ids, json_decode($arguments, true));
                }
                $this->walk($found, $action['state'] ?? null);
                $this->walkArguments($ids, $action['state'] ?? null);
                $type = $this->targetType($action);
                $id = is_array($action['result'] ?? null) ? ($action['result']['id'] ?? null) : null;
                if ($type !== null && is_int($id)) {
                    $this->add($found, $type, $id);
                }
                $targetId = $this->targetId($action);
                if ($type !== null && $targetId !== null) {
                    $this->add($found, $type, $targetId);
                }
            }
        }

        foreach ($this->entityRefsReader->read($ids) as $ref) {
            $mention = new EntityMentionDto($ref->type, $ref->id);
            $found[$mention->key()] = $mention;
        }

        return array_values($found);
    }

    /**
     * The type of the entity an action works on.
     *
     * @param array<array-key, mixed> $action
     */
    private function targetType(array $action): ?string
    {
        /** @var mixed $type */
        $type = $action['entityType'] ?? null;

        return is_string($type) ? $type : null;
    }

    /**
     * The id of the entity an action works on, from its state.
     *
     * @param array<array-key, mixed> $action
     */
    private function targetId(array $action): ?int
    {
        $state = $action['state'] ?? null;
        if (!is_array($state)) {
            return null;
        }
        return isset($state['id']) && is_int($state['id']) ? $state['id'] : null;
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
     * The ids of the fields named `<name>Id` or `<name>Ids`, at any depth.
     *
     * @param list<int> $ids
     */
    private function walkArguments(array &$ids, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $key => $item) {
            if (is_string($key) && preg_match('/^\w+?Ids?$/', $key) === 1) {
                foreach (is_array($item) ? $item : [$item] as $id) {
                    if (is_int($id)) {
                        $ids[] = $id;
                    }
                }
            }
            $this->walkArguments($ids, $item);
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
