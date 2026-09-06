<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

/**
 * One country of the management list.
 *
 * `titles` carries every interface language at once: the list and the edit form
 * are answered by the same request.
 */
readonly class ManageCountryDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     */
    public function __construct(
        public int $id,
        public string $title,
        public array $titles,
        public float $latitude,
        public float $longitude,
        public int $usages,
        public int $cities,
    ) {
    }
}
