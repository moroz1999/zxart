<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\Dto\ListedFileDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A production as the assistant sees it: the fields its actions write, under
 * the names they take them, and what an edit needs to know besides.
 *
 * @psalm-api
 */
final readonly class ProdDto
{
    /**
     * @param list<string> $languages language codes
     * @param list<string> $hardware hardware codes
     * @param list<int> $categoryIds
     * @param list<int> $groupIds groups and group aliases
     * @param list<int> $publisherIds groups, group aliases, authors and author aliases
     * @param list<string> $tags tag titles
     * @param list<int> $compilationItemIds productions and releases
     * @param list<int> $seriesProdIds
     * @param list<EntityRefDto> $refs the entities the ids name
     * @param list<CreditDto> $authors
     * @param list<FoundReleaseDto> $releases
     * @param list<ListedFileDto> $files
     * @param list<EntityRefDto> $inSeries the series productions it is a part of
     * @param list<EntityRefDto> $inCompilations the compilations that include it
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $altTitle,
        /** 0 when unknown */
        public int $year,
        public string $description,
        public string $instructions,
        public string $legalStatus,
        public string $externalLink,
        public string $youtubeId,
        /** 0 when none */
        public int $partyId,
        /** 0 when none */
        public int $partyPlace,
        public string $compo,
        public array $languages,
        public array $hardware,
        public array $categoryIds,
        public array $groupIds,
        public array $publisherIds,
        public array $tags,
        public array $compilationItemIds,
        public array $seriesProdIds,
        public array $refs,
        public array $authors,
        public array $releases,
        public array $files,
        public array $inSeries,
        public array $inCompilations,
    ) {
    }
}
