<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Authors\Dto\AuthorshipInputDto;

/**
 * The JSON part of a batch upload: the values every new production shares.
 */
final readonly class ProdBatchUploadRequestDto
{
    /**
     * @param list<AuthorshipInputDto> $authors
     */
    public function __construct(
        public ProdFieldsDto $fields = new ProdFieldsDto(),
        public array $authors = [],
    ) {
    }
}
