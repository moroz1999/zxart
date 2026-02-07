<?php

declare(strict_types=1);

namespace ZxArt\Releases\Rest;

use ZxArt\Shared\Dto\AuthorDto;

readonly class ReleaseRestDto
{
    /**
     * @param AuthorDto[] $authors
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public ?string $year,
        public float $votes,
        public array $authors,
    ) {
    }
}
