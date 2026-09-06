<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * A create or update request for one city.
 *
 * `countryId` is required on a creation and on an update alike: it states which
 * country the city belongs to, so an update naming another one moves it there.
 * A city without a country would be unreachable, which is why the field has no
 * usable default.
 *
 * @see CountrySaveDto
 */
readonly class CitySaveDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     */
    public function __construct(
        public array $titles = [],
        public ?int $id = null,
        public ?int $countryId = null,
        public float $latitude = 0.0,
        public float $longitude = 0.0,
    ) {
    }
}
