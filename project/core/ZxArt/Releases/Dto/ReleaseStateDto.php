<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\AttachedFileDto;

/**
 * Everything an editor may change on a release, with its modification time to
 * tell whether it changed since it was read.
 *
 * @psalm-api
 */
final readonly class ReleaseStateDto
{
    /**
     * @param AuthorshipDto[] $authors
     * @param string $fileName the release file's name, empty when there is none
     * @param ?int $fileSize the release file's size in bytes, null when it is not stored here
     * @param array<string, AttachedFileDto[]> $files each file list, by its kind (`ReleaseFileKind`)
     * @param ImportOriginDto[] $importOrigins
     */
    public function __construct(
        public int $id,
        public int $modifiedAt,
        public ReleaseFieldsDto $fields,
        public array $authors,
        public string $fileName,
        public ?int $fileSize,
        public array $files,
        public array $importOrigins,
    ) {
    }
}
