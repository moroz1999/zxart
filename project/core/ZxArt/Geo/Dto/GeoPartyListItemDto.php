<?php

declare(strict_types=1);

namespace ZxArt\Geo\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Geo\Rest\GeoPartyListItemRestDto;

#[Map(target: GeoPartyListItemRestDto::class)]
readonly class GeoPartyListItemDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?int $countryId,
        public ?string $countryTitle,
        public ?string $countryUrl,
        public ?int $cityId,
        public ?string $cityTitle,
        public ?string $cityUrl,
        public int $entries,
    ) {
    }
}
