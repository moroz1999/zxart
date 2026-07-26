<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\PageMetadata\PageMetadataDto;

readonly class ProdCoreDto
{
    /**
     * @param ProdCategoryPathDto[] $categoriesPaths
     * @param ProdLanguageInfoDto[] $languages
     * @param ProdHardwareInfoDto[] $hardware
     * @param ProdLinkInfoDto[]     $links
     * @param ProdAuthorInfoDto[]   $authors
     * @param ProdGroupRefDto[]     $publishers
     * @param ProdGroupRefDto[]     $groups
     * @param ProdTagRefDto[]       $tags
     */
    public function __construct(
        public int $elementId,
        public string $title,
        public string $altTitle,
        public string $prodUrl,
        public string $h1,
        public string $metaTitle,
        public int $year,
        public string $legalStatus,
        public string $legalStatusLabel,
        public string $externalLink,
        public string $youtubeId,
        public string $generatedDescription,
        public string $dateCreated,
        public bool $canAddRelease,
        public int $downloadsCount,
        public int $playsCount,
        public array $categoriesPaths,
        public array $languages,
        public array $hardware,
        public array $links,
        public ?ProdPartyInfoDto $party,
        public array $authors,
        public array $publishers,
        public array $groups,
        public array $tags,
        public ProdVotingDto $voting,
        public ?ProdSubmitterDto $submitter,
        public ProdTabsDto $tabs,
        public PageMetadataDto $metadata,
    ) {
    }
}
