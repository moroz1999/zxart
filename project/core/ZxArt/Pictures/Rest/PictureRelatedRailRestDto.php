<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class PictureRelatedRailRestDto
{
    /**
     * @param PictureRestDto[] $items
     */
    public function __construct(
        public string $kind,
        public string $title,
        public ?string $kicker,
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
