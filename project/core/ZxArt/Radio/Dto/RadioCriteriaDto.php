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
        public ?float $minRating,
        public ?float $maxRating,
        public array $yearsInclude,
        public array $yearsExclude,
        public array $countriesInclude,
        public array $countriesExclude,
        public array $formatGroupsInclude,
        public array $formatGroupsExclude,
        public array $formatsInclude,
        public array $formatsExclude,
        public array $prodCategoriesInclude,
        public ?int $bestVotesLimit,
        public ?int $maxPlays,
        public ?int $minPartyPlace,
        public ?bool $requireGame,
        public ?bool $hasParty,
        public ?int $notVotedByUserId,
    ) {
    }
}
