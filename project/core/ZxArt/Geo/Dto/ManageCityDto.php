<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * One city of the management list, with the country it belongs to.
 *
 * @see ManageCountryDto
 */
readonly class ManageCityDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     */
    public function __construct(
        public int $id,
        public int $countryId,
        public string $title,
        public array $titles,
        public float $latitude,
        public float $longitude,
        public int $usages,
    ) {
    }
}
