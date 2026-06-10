<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\ObjectMapper\Transform\MapCollection;

readonly class PartyCoreRestDto
{
    /**
     * @param PartyLinkRestDto[]       $links
     * @param PartyCompoRestDto[]      $compos
     * @param PartyEditionRestDto[]    $editions
     * @param PartyBreadcrumbRestDto[] $breadcrumbs
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        public string $originalName,
        public string $url,
        public string $imageUrl,
        public ?string $year,
        public PartyLocationRestDto $location,
        #[Map(transform: MapCollection::class)]
        public array $links,
        #[Map(transform: MapCollection::class)]
        public array $compos,
        #[Map(transform: MapCollection::class)]
        public array $editions,
        public string $zipUrl,
        public PartyCountersRestDto $counters,
        public PartyTabsRestDto $tabs,
        #[Map(transform: MapCollection::class)]
        public array $breadcrumbs,
    ) {
    }
}
