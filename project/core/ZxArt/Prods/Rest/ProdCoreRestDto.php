<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class ProdCoreRestDto
{
    /**
     * @param ProdCategoryPathRestDto[] $categoriesPaths
     * @param ProdLanguageInfoRestDto[] $languages
     * @param ProdHardwareInfoRestDto[] $hardware
     * @param ProdLinkInfoRestDto[]     $links
     * @param ProdAuthorInfoRestDto[]   $authors
     * @param ProdGroupRefRestDto[]     $publishers
     * @param ProdGroupRefRestDto[]     $groups
     * @param ProdTagRefRestDto[]       $tags
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
        public string $description,
        public bool $htmlDescription,
        public string $instructions,
        public string $generatedDescription,
        public string $dateCreated,
        public string $catalogueYearUrl,
        #[Map(transform: MapCollection::class)]
        public array $categoriesPaths,
        #[Map(transform: MapCollection::class)]
        public array $languages,
        #[Map(transform: MapCollection::class)]
        public array $hardware,
        #[Map(transform: MapCollection::class)]
        public array $links,
        public ?ProdPartyInfoRestDto $party,
        #[Map(transform: MapCollection::class)]
        public array $authors,
        #[Map(transform: MapCollection::class)]
        public array $publishers,
        #[Map(transform: MapCollection::class)]
        public array $groups,
        #[Map(transform: MapCollection::class)]
        public array $tags,
        public ProdVotingRestDto $voting,
        public ?ProdSubmitterRestDto $submitter,
    ) {
    }
}
