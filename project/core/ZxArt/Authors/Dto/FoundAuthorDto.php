<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use ZxArt\Shared\References\EntityRefDto;

/**
 * An author a typed search found, with what tells it apart.
 *
 * @psalm-api
 */
final readonly class FoundAuthorDto
{
    /**
     * @param list<EntityRefDto> $aliases
     * @param list<EntityRefDto> $groups the groups (or group aliases) the author or its aliases are members of
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $realName,
        public ?EntityRefDto $country,
        public ?EntityRefDto $city,
        public array $aliases,
        public array $groups,
    ) {
    }
}
