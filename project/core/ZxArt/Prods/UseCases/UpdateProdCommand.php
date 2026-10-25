<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdFileUploadDto;

/**
 * A list left `null` keeps what the production holds; an empty list clears
 * it. The uploaded files are added to the lists of their kinds.
 */
final readonly class UpdateProdCommand
{
    /**
     * @param list<AuthorshipInputDto>|null $authors
     * @param list<ImportOriginDto>|null $importOrigins
     * @param list<ProdFileUploadDto> $files
     */
    public function __construct(
        public int $prodId,
        public ProdFieldsDto $fields,
        public ?array $authors = null,
        public ?array $importOrigins = null,
        public array $files = [],
    ) {
    }
}
