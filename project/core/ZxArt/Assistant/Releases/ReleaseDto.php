<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\Dto\ListedFileDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A release as the assistant sees it: the fields its actions write, under the
 * names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class ReleaseDto
{
    /**
     * @param list<string> $releaseFormats release format codes
     * @param list<string> $languages language codes
     * @param list<string> $hardware hardware codes the release needs beyond its production's
     * @param list<int> $publisherIds groups, group aliases, authors and author aliases
     * @param list<EntityRefDto> $refs the entities the ids name
     * @param list<CreditDto> $authors
     * @param list<ListedFileDto> $files
     * @param list<string> $prodHardware hardware codes the production requires, which the release inherits
     * @param list<string> $prodLanguages language codes of the production, which a release without its own inherits
     */
    public function __construct(
        public int $id,
        public string $title,
        /** 0 when unknown */
        public int $year,
        public string $version,
        public string $description,
        public string $releaseType,
        public array $releaseFormats,
        public array $languages,
        public array $hardware,
        public array $publisherIds,
        public int $prodId,
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
