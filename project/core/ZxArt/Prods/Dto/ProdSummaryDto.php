<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

readonly class ProdSummaryDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public int $year,
        public string $legalStatus,
        public string $legalStatusLabel,
        public float $votes,
        public int $votesAmount,
        public ?string $imageUrl,
    ) {
    }
}
