<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\AttachedFileDto;

/**
 * Everything an editor may change on a production, with its modification time
 * to tell whether it changed since it was read.
 *
 * @psalm-api
 */
final readonly class ProdStateDto
{
    /**
     * @param AuthorshipDto[] $authors
     * @param int[] $releaseIds
     * @param array<string, AttachedFileDto[]> $files each file list, by its kind (`ProdFileKind`)
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public ProdFieldsDto $fields,
        public array $authors,
        public array $releaseIds,
        public array $files,
        public array $importOrigins,
    ) {
    }
}
