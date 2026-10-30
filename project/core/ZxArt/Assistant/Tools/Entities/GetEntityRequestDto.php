<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

/**
 * @psalm-api
 */
final readonly class GetEntityRequestDto
{
    /**
     * @param int $id the id of the entity
     */
    public function __construct(
        public int $id,
    ) {
    }
}
