<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdFileUploadDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Values typed once for a production per uploaded software file.
 */
final readonly class BatchUploadProdsCommand
{
    /**
     * @param list<UploadedFileDto> $releaseFiles one production, with one release, per file
     * @param list<AuthorshipInputDto> $authors
     * @param list<ProdFileUploadDto> $files added to every production
     */
    public function __construct(
        public ProdFieldsDto $fields,
        public array $releaseFiles,
        public array $authors = [],
        public array $files = [],
    ) {
    }
}
