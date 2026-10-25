<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Authors\Dto\AuthorshipInputDto;

/**
 * The JSON part of a request that makes a release per uploaded file: the
 * values every new release shares.
 */
final readonly class ReleaseCreateFromFilesRequestDto
{
    /**
     * @param list<AuthorshipInputDto> $authors
     */
    public function __construct(
        public ReleaseFieldsDto $fields = new ReleaseFieldsDto(),
        public array $authors = [],
    ) {
    }
}
