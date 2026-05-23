<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Dto\ProdCategoryPathDto;
use ZxArt\Releases\Rest\ReleaseProdRefRestDto;

#[Map(target: ReleaseProdRefRestDto::class)]
readonly class ReleaseProdRefDto
{
    /**
     * @param string[]              $authorNames
     * @param ProdCategoryPathDto[] $categoriesPaths
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public int $year,
        public array $authorNames,
        public ?string $thumbnailUrl,
        public array $categoriesPaths,
    ) {
    }
}
