<?php

declare(strict_types=1);

namespace ZxArt\Geo\Rest;

readonly class ManageCityRestDto
{
    /**
     * @param array<int, string> $titles keyed by language id, so the management
     *        form can address a language directly
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
