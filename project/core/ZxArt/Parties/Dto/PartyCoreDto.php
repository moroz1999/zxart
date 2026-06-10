<?php

declare(strict_types=1);

namespace ZxArt\Parties\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Parties\Rest\PartyCoreRestDto;

#[Map(target: PartyCoreRestDto::class)]
readonly class PartyCoreDto
{
    /**
     * @param PartyLinkDto[]       $links
     * @param PartyCompoDto[]      $compos
     * @param PartyEditionDto[]    $editions
     * @param PartyBreadcrumbDto[] $breadcrumbs
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $abbreviation,
        public string $originalName,
        public string $url,
        public string $imageUrl,
        public ?string $year,
        public PartyLocationDto $location,
        public array $links,
        public array $compos,
        public array $editions,
        public string $zipUrl,
        public PartyCountersDto $counters,
        public PartyTabsDto $tabs,
        public array $breadcrumbs,
    ) {
    }
}
