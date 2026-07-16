<?php

declare(strict_types=1);

namespace ZxArt\Shared;

/**
 * Minimal reference to a business entity (id + display title) used by relation
 * pickers in forms. Mirrors the Angular `EntityRef`.
 */
final readonly class EntityRefDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
