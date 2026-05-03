<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Shared\EntityType;

readonly class AuthorListItemRestDto
{
    /**
     * @param array<array{id: int, title: string, url: string}> $groups
     */
    public function __construct(
        public int $id,
        public string $url,
        #[Map(transform: [self::class, 'mapEntityType'])]
        public string $entityType,
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

    public static function mapEntityType(EntityType $entityType): string
    {
        return $entityType->value;
    }
}
