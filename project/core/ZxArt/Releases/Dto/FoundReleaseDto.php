<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A release a typed search found, with what tells it apart.
 *
 * @psalm-api
 */
final readonly class FoundReleaseDto
{
    /**
     * @param list<string> $formats
     * @param list<CreditDto> $authors
     * @param list<EntityRefDto> $publishers authors or groups
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?int $year,
        public ?EntityRefDto $prod,
        public string $releaseType,
        public array $formats,
        /** the release file's name; empty without a file */
        public string $fileName,
        public array $authors,
        public array $publishers,
    ) {
    }
}
