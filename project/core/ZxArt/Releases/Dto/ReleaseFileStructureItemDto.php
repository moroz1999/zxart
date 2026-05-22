<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Releases\Rest\ReleaseFileStructureItemRestDto;

#[Map(target: ReleaseFileStructureItemRestDto::class)]
readonly class ReleaseFileStructureItemDto
{
    /**
     * @param ReleaseFileStructureItemDto[] $items
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
        public array $items,
    ) {
    }
}
