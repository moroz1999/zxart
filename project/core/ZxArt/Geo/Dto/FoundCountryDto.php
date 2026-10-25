<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * @psalm-api
 */
final readonly class FoundCountryDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {
    }
}
