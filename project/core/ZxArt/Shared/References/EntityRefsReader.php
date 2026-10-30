<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use structureManager;

/**
 * Names the entities of ids as refs.
 *
 * @psalm-api
 */
final readonly class EntityRefsReader
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * Each id once, in the order given; an id of nothing, or of no entity, is
     * left out.
     *
     * @param int[] $ids
     * @return list<EntityRefDto>
     */
    public function read(array $ids): array
    {
        $refs = [];
        foreach (array_unique($ids) as $id) {
            $element = $id > 0 ? $this->structureManager->getElementById($id) : null;
            $ref = $element !== null ? EntityRefs::tryOf($element) : null;
            if ($ref !== null) {
                $refs[] = $ref;
            }
        }

        return $refs;
    }
}
