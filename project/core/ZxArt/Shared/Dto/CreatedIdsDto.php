<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * The elements a write created, in the order it created them.
 */
final readonly class CreatedIdsDto
{
    /**
     * @param list<int> $ids
     */
    public function __construct(
        public array $ids,
    ) {
    }
}
