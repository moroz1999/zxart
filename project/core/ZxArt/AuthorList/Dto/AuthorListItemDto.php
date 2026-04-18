<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Dto;

use ZxArt\Shared\EntityType;

readonly class AuthorListItemDto
{
    /**
     * @param array<array{id: int, title: string, url: string}> $groups
     */
    public function __construct(
        public int $id,
        public string $url,
        public EntityType $entityType,
        public string $title,
        public string $realName,
        public ?string $realNameUrl,
        public array $groups,
        public ?int $countryId,
        public ?string $countryTitle,
        public ?string $countryUrl,
        public ?int $cityId,
        public ?string $cityTitle,
        public ?string $cityUrl,
        public float $musicRating,
        public float $graphicsRating,
    ) {
    }
}
