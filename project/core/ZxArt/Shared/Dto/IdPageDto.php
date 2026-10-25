<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * One page of a search: the ids on it and how many there are in all.
 *
 * @psalm-api
 */
final readonly class IdPageDto
{
    /**
     * @param int[] $ids
     */
    public function __construct(
        public int $total,
        public array $ids,
    ) {
    }
}
