<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * What a search narrows by; every filter given must hold.
 *
 * @psalm-api
 */
final readonly class AuthorSearchFilterDto
{
    public function __construct(
        /** a part of the nickname, of the real name in any language or of an alias */
        public ?string $name = null,
        public ?int $countryId = null,
        public ?int $cityId = null,
        public ?int $groupId = null,
    ) {
    }
}
