<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\Dto\ListedFileDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A release as an edit needs to see it.
 *
 * @psalm-api
 */
final readonly class ReleaseCardDto
{
    /**
     * @param list<EntityRefDto> $refs the entities the fields name by id
     * @param list<CreditDto> $authors
     * @param list<ListedFileDto> $files
     * @param list<string> $prodHardware hardware codes the production requires, which the release inherits
     * @param list<string> $prodLanguages language codes of the production, which a release without its own inherits
     */
    public function __construct(
        public int $id,
        /** the fields as `update_release` takes them */
        public ReleaseFieldsDto $fields,
        public array $refs,
        public array $authors,
        public array $files,
        /** the release file's name; empty without a file */
        public string $fileName,
        /** in bytes, when the file is known */
        public ?int $fileSize,
        public array $prodHardware,
        public array $prodLanguages,
    ) {
    }
}
