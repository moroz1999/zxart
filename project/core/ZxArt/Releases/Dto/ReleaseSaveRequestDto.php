<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * The JSON part of a release update request. A list left out keeps what the
 * release holds; an empty list clears it.
 */
final readonly class ReleaseSaveRequestDto
{
    /**
     * @param list<AuthorshipInputDto>|null $authors
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public ReleaseFieldsDto $fields = new ReleaseFieldsDto(),
        public ?array $authors = null,
        public ?array $importOrigins = null,
    ) {
    }
}
