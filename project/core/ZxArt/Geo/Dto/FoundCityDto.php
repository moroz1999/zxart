<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * @psalm-api
 */
final readonly class FoundCityDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $countryId,
        public string $countryTitle,
    ) {
    }
}
