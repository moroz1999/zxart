<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\EntityType;

readonly class GroupListItemRestDto
{
    public function __construct(
        public int $id,
        public string $url,
        #[Map(transform: [self::class, 'mapEntityType'])]
        public string $entityType,
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

    public static function mapEntityType(EntityType $entityType): string
    {
        return $entityType->value;
    }
}
