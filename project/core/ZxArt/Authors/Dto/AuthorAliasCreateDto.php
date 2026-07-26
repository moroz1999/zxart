<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

final readonly class AuthorAliasCreateDto
{
    public function __construct(
        public int $authorId,
        public string $title,
        public string $startDate,
        public string $endDate,
        public bool $displayInMusic,
        public bool $displayInGraphics,
    ) {
    }
}
