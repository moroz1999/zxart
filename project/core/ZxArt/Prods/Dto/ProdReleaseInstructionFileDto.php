<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdReleaseInstructionFileRestDto;
use ZxArt\Prods\Dto\ProdGroupRefDto;

#[Map(target: ProdReleaseInstructionFileRestDto::class)]
readonly class ProdReleaseInstructionFileDto
{
    /**
     * @param ProdGroupRefDto[] $releaseBy
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $fileName,
        public string $downloadUrl,
        public string $releaseTitle,
        public string $releaseUrl,
        public int $releaseYear,
        public ?string $releaseTypeLabel,
        public array $releaseBy,
    ) {
    }
}
