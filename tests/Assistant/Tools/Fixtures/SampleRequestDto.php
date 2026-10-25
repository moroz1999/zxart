<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Fixtures;

final readonly class SampleRequestDto
{
    /**
     * @param string $title the title to set
     * @param ?int $year the year it came out
     * @param ?int[] $categoryIds the categories, replacing the current ones
     * @param SampleItemDto[] $credits
     * @param SampleKind[] $kinds
     */
    public function __construct(
        public string $title,
        public ?int $year = null,
        public ?array $categoryIds = null,
        public ?SampleKind $kind = null,
        public bool $html = false,
        public float $rating = 0.0,
        public array $credits = [],
        public array $kinds = [],
    ) {
    }
}
