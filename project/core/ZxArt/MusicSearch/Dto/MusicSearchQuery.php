<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch\Dto;

use ZxArt\MusicSearch\MusicSearchSort;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;

readonly class MusicSearchQuery
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
        public ?string $formatGroup,
        public ?string $format,
        public bool $realtimeOnly,
        public array $tagsInclude,
        public array $tagsExclude,
        public array $authorCountryIds,
        public array $authorCityIds,
        public PictureSearchResultsType $resultsType,
        public MusicSearchSort $sortParameter,
        public PictureSearchOrder $sortOrder,
        public int $start,
        public int $limit,
    ) {
    }
}
