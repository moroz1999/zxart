<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use ZxArt\Search\Services\AliasHandling;
use ZxArt\Search\Services\PhraseSearchMode;
use ZxArt\Search\Services\PhraseSearchType;

final readonly class PhraseSearchQueryDto
{
    /**
     * @param string $phrase as the user typed it
     * @param PhraseSearchType[] $types with merged aliases, authors and groups take in their aliases
     * @param int $limit results of the page, of all types together
     * @param int $offset where a page starts in the alphabetical list (paged mode only)
     * @param bool $contentMatching search the texts too, not only the titles
     * @param bool $closestFirst order the results of all types together by how close their title is to the phrase
     * @param ?int $languageId the language the elements load in; the current one when not given
     */
    public function __construct(
        public string $phrase,
        public array $types,
        public int $limit,
        public PhraseSearchMode $mode,
        public int $offset = 0,
        public bool $contentMatching = false,
        public AliasHandling $aliases = AliasHandling::Separate,
        public bool $closestFirst = false,
        public ?int $languageId = null,
    ) {
    }
}
