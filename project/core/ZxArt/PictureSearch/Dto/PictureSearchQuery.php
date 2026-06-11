<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch\Dto;

use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\PictureSearchSort;

readonly class PictureSearchQuery
{
    /**
     * @param string[] $tagsInclude
     * @param string[] $tagsExclude
     * @param int[] $authorCountryIds
     * @param int[] $authorCityIds
     */
    public function __construct(
        public ?string $titleWord,
        public ?int $startYear,
        public ?int $endYear,
        public ?float $minRating,
        public ?int $minPartyPlace,
        public ?string $pictureType,
        public bool $realtimeOnly,
        public bool $inspirationOnly,
        public bool $stagesOnly,
        public array $tagsInclude,
        public array $tagsExclude,
        public array $authorCountryIds,
        public array $authorCityIds,
        public PictureSearchResultsType $resultsType,
        public PictureSearchSort $sortParameter,
        public PictureSearchOrder $sortOrder,
        public int $start,
        public int $limit,
    ) {
    }
}
