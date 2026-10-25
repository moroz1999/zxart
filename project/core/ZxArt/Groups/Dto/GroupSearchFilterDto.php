<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

/**
 * What a search narrows by; every filter given must hold.
 *
 * @psalm-api
 */
final readonly class GroupSearchFilterDto
{
    public function __construct(
        /** a part of the title, of the abbreviation or of an alias */
        public ?string $name = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?string $type = null,
    ) {
    }
}
