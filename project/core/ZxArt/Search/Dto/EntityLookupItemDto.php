<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

/**
 * An entity a relation picker offers for a phrase, named the way it is told apart.
 */
final readonly class EntityLookupItemDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
