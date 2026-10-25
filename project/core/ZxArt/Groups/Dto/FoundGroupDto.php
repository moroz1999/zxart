<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use ZxArt\Shared\References\EntityRefDto;

/**
 * A group a typed search found, with what tells it apart.
 *
 * @psalm-api
 */
final readonly class FoundGroupDto
{
    /**
     * @param list<EntityRefDto> $aliases
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        public string $type,
        public ?EntityRefDto $country,
        public ?EntityRefDto $city,
        public array $aliases,
    ) {
    }
}
