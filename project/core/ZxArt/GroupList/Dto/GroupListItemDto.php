<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Dto;

use ZxArt\Shared\EntityType;

readonly class GroupListItemDto
{
    public function __construct(
        public int $id,
        public string $url,
        public EntityType $entityType,
        public string $title,
        public string $groupType,
        public ?string $realGroupTitle,
        public ?string $realGroupUrl,
        public ?int $countryId,
        public ?string $countryTitle,
        public ?string $countryUrl,
        public ?int $cityId,
        public ?string $cityTitle,
        public ?string $cityUrl,
    ) {
    }
}
