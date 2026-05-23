<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

use Symfony\Component\ObjectMapper\Transform\MapCollection;
use Symfony\Component\ObjectMapper\Attribute\Map;

readonly class ReleaseFileStructureItemRestDto
{
    /**
     * @param ReleaseFileStructureItemRestDto[] $items
     */
    public function __construct(
        public int $id,
        public string $fileName,
        public int $size,
        public string $type,
        public string $typeLabel,
        public bool $viewable,
        public ?string $viewUrl,
        public ?string $downloadUrl,
        #[Map(transform: MapCollection::class)]
        public array $items,
    ) {
    }
}
