<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityRefsReader;

/**
 * Mentions of the entities ids name, for previews. An id of nothing stays a
 * number, so the preview still shows what the call gave.
 *
 * @psalm-api
 */
final readonly class Mentions
{
    public function __construct(
        private EntityRefsReader $entityRefsReader,
    ) {
    }

    public function one(int $id): EntityMentionDto|int
    {
        return $this->list([$id])[0];
    }

    /**
     * @param list<int> $ids
     * @return list<EntityMentionDto|int> in the order given
     */
    public function list(array $ids): array
    {
        $mentions = [];
        foreach ($this->entityRefsReader->read($ids) as $ref) {
            $mentions[$ref->id] = new EntityMentionDto($ref->type, $ref->id);
        }

        return array_map(static fn(int $id): EntityMentionDto|int => $mentions[$id] ?? $id, $ids);
    }
}
