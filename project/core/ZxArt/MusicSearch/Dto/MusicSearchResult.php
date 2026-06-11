<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch\Dto;

use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\Tunes\Dto\TuneDto;

readonly class MusicSearchResult
{
    /**
     * @param TuneDto[] $tunes
     * @param AuthorListItemDto[] $authors
     * @param string[] $formats
     */
    public function __construct(
        public int $totalAmount,
        public PictureSearchResultsType $resultsType,
        public array $tunes,
        public array $authors,
        public array $formats,
        public string $apiUrl,
        public string $zipUrl,
    ) {
    }
}
