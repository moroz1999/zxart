<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;

/**
 * The JSON part of a production update request. A list left out keeps what
 * the production holds; an empty list clears it.
 */
final readonly class ProdSaveRequestDto
{
    /**
     * @param list<AuthorshipInputDto>|null $authors
     * @param list<ImportOriginDto>|null $importOrigins
     */
    public function __construct(
        public ProdFieldsDto $fields = new ProdFieldsDto(),
        public ?array $authors = null,
        public ?array $importOrigins = null,
    ) {
    }
}
