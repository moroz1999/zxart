<?php

declare(strict_types=1);

namespace ZxArt\Shared\References;

use ZxArt\Shared\EntityType;

/**
 * An entity named by its type, id and title.
 *
 * @psalm-api
 */
final readonly class EntityRefDto
{
    public function __construct(
        public EntityType $type,
        public int $id,
        public string $title,
    ) {
    }
}
