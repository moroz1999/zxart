<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Dto;

/**
 * @psalm-api
 */
final readonly class EntityIdRequestDto
{
    /**
     * @param int $id the id of the entity
     */
    public function __construct(
        public int $id,
    ) {
    }
}
