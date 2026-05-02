<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdFileRestDto;

#[Map(target: ProdFileRestDto::class)]
readonly class ProdFileDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $author,
        public string $fileName,
        public ?string $imageUrl,
        public ?string $fullImageUrl,
        public string $downloadUrl,
        public bool $isImage,
    ) {
    }
}
