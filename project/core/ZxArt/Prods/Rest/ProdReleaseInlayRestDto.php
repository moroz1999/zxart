<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdReleaseInlayRestDto
{
    /**
     * @param ProdGroupRefRestDto[] $releaseBy
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $imageUrl,
        public ?string $fullImageUrl,
        public string $downloadUrl,
        public string $releaseTitle,
        public string $releaseUrl,
        public int $releaseYear,
        public ?string $releaseTypeLabel,
        #[Map(transform: MapCollection::class)]
        public array $releaseBy,
    ) {
    }
}
