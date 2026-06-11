<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch\Dto;

use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\Pictures\Dto\PictureDto;

readonly class PictureSearchResult
{
    /**
     * @param PictureDto[] $pictures
     * @param AuthorListItemDto[] $authors
     */
    public function __construct(
        public int $totalAmount,
        public PictureSearchResultsType $resultsType,
        public array $pictures,
        public array $authors,
        public string $apiUrl,
        public string $zipUrl,
    ) {
    }
}
