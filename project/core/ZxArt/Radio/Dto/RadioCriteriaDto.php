<?php

declare(strict_types=1);

namespace ZxArt\Radio\Dto;

readonly class RadioCriteriaDto
{
    /**
     * @param int[] $yearsInclude
     * @param int[] $yearsExclude
     * @param int[] $countriesInclude
     * @param int[] $countriesExclude
     * @param string[] $formatGroupsInclude
     * @param string[] $formatGroupsExclude
     * @param string[] $formatsInclude
     * @param string[] $formatsExclude
     * @param int[] $prodCategoriesInclude
     */
    public function __construct(
        public ?float $minRating = null,
        public ?float $maxRating = null,
        public array $yearsInclude = [],
        public array $yearsExclude = [],
        public array $countriesInclude = [],
        public array $countriesExclude = [],
        public array $formatGroupsInclude = [],
        public array $formatGroupsExclude = [],
        public array $formatsInclude = [],
        public array $formatsExclude = [],
        public array $prodCategoriesInclude = [],
        public ?int $bestVotesLimit = null,
        public ?int $maxPlays = null,
        public ?int $minPartyPlace = null,
        public ?bool $requireGame = null,
        public ?bool $hasParty = null,
        public ?int $notVotedByUserId = null,
    ) {
    }
}
