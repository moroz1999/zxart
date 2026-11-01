<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat\Dto;

/**
 * The page the user is on while writing.
 */
final readonly class PageDto
{
    public function __construct(
        public string $url,
        public ?string $entityType = null,
        public ?int $entityId = null,
    ) {
    }
}
