<?php

declare(strict_types=1);

namespace ZxArt\Parties\Services;

use partyElement;
use structureManager;
use ZxArt\Parties\Dto\PartyCompoDto;
use ZxArt\Parties\Dto\PartyCoreDto;
use ZxArt\Parties\Dto\PartyCountersDto;
use ZxArt\Parties\Dto\PartyEditionDto;
use ZxArt\Parties\Dto\PartyLinkDto;
use ZxArt\Parties\Dto\PartyLocationDto;
use ZxArt\Parties\Dto\PartyLocationItemDto;
use ZxArt\Parties\Dto\PartyTabsDto;
use ZxArt\Parties\Repositories\PartiesRepository;
use ZxArt\Parties\Repositories\PartyAuthorsRepository;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArtItem;
use ZxArt\PageMetadata\PageMetadataService;

readonly class PartyDetailsService
{
    /**
     * Each compo medium and the party element accessor that returns its works grouped by compo.
     */
    private const array COMPO_MEDIA = [
        'prod' => 'getProdsCompos',
        'picture' => 'getPicturesCompos',
        'music' => 'getTunesCompos',
    ];

    public function __construct(
        private \ZxArt\Urls\EntityUrlResolver $entityUrlResolver,
        private structureManager $structureManager,
        private PartyCompoNameResolver $compoNameResolver,
        private PartiesRepository $partiesRepository,
        private PartyAuthorsRepository $partyAuthorsRepository,
        private PageMetadataService $pageMetadataService,
    ) {
    }

    public function getDetails(int $partyId): PartyCoreDto
    {
        $party = $this->structureManager->getElementById($partyId);
        if (!$party instanceof partyElement) {
            throw new ProdDetailsException('Party not found', 404);
        }

        $compos = $this->buildCompos($party);
        $counters = $this->buildCounters($party, $compos);

        return new PartyCoreDto(
            id: (int)$party->getId(),
            title: $this->decode((string)$party->getTitle()),
            abbreviation: $this->decode((string)$party->abbreviation),
            originalName: $this->decode((string)$party->originalName),
            url: $this->entityUrlResolver->urlFor($party),
            imageUrl: $party->getImageUrl(),
            year: ($year = $party->getYear()) !== '' ? $year : null,
            location: $this->buildLocation($party),
            links: $this->buildLinks($party),
            compos: $compos,
            editions: $this->buildEditions($party),
            zipUrl: $party->getSaveUrl(),
            counters: $counters,
            tabs: $this->buildTabs($counters),
            metadata: $this->pageMetadataService->getForPath('/party/' . $partyId),
        );
    }

    /**
     * @return PartyCompoDto[]
     */
    private function buildCompos(partyElement $party): array
    {
        $compos = [];
        foreach (self::COMPO_MEDIA as $medium => $method) {
            /** @var array<string, ZxArtItem[]> $grouped */
            $grouped = $party->{$method}();
            foreach ($grouped as $compoType => $entries) {
                $compoType = (string)$compoType;
                $compos[] = new PartyCompoDto(
                    compoType: $compoType,
                    medium: $medium,
                    name: $this->compoNameResolver->resolve($medium, $compoType),
                    count: count($entries),
                );
            }
        }
        return $compos;
    }

    /**
     * @param PartyCompoDto[] $compos
     */
    private function buildCounters(partyElement $party, array $compos): PartyCountersDto
    {
        $byMedium = ['prod' => 0, 'picture' => 0, 'music' => 0];
        $entries = 0;

        foreach (self::COMPO_MEDIA as $medium => $method) {
            /** @var array<string, ZxArtItem[]> $grouped */
            $grouped = $party->{$method}();
            foreach ($grouped as $items) {
                $itemsCount = count($items);
                $entries += $itemsCount;
                $byMedium[$medium] += $itemsCount;
            }
        }

        return new PartyCountersDto(
            compos: count($compos),
            entries: $entries,
            authors: $this->partyAuthorsRepository->countByPartyId($party->getId()),
            pictures: $byMedium['picture'],
            tunes: $byMedium['music'],
            prods: $byMedium['prod'],
            comments: (int)$party->getCommentsAmount(),
        );
    }

    private function buildTabs(PartyCountersDto $counters): PartyTabsDto
    {
        $hasWorks = $counters->entries > 0;
        return new PartyTabsDto(
            hasOverview: $hasWorks,
            hasCompos: $hasWorks,
            hasActivity: $hasWorks || $counters->comments > 0,
        );
    }

    private function buildLocation(partyElement $party): PartyLocationDto
    {
        $city = null;
        if ($cityElement = $party->getCityElement()) {
            $city = new PartyLocationItemDto(
                title: $this->decode((string)$cityElement->title),
                url: $this->entityUrlResolver->urlFor($cityElement),
            );
        }
        $country = null;
        if ($countryElement = $party->getCountryElement()) {
            $country = new PartyLocationItemDto(
                title: $this->decode((string)$countryElement->title),
                url: $this->entityUrlResolver->urlFor($countryElement),
            );
        }
        return new PartyLocationDto(city: $city, country: $country);
    }

    /**
     * @return PartyLinkDto[]
     */
    private function buildLinks(partyElement $party): array
    {
        $links = [];
        if (($website = (string)$party->website) !== '') {
            $links[] = new PartyLinkDto(url: $website, label: $this->extractDomain($website));
        }
        return $links;
    }

    /**
     * @return PartyEditionDto[]
     */
    private function buildEditions(partyElement $party): array
    {
        $abbreviation = (string)$party->abbreviation;
        $ids = $this->partiesRepository->getIdsByAbbreviation($abbreviation);
        if ($ids === []) {
            $ids = [(int)$party->getId()];
        }

        $editions = [];
        foreach ($ids as $id) {
            $edition = $this->structureManager->getElementById($id);
            if (!$edition instanceof partyElement) {
                continue;
            }
            $year = $edition->getYear();
            if ($year === '') {
                continue;
            }
            $editions[] = new PartyEditionDto(
                id: (int)$edition->getId(),
                year: $year,
                url: $this->entityUrlResolver->urlFor($edition),
                current: (int)$edition->getId() === (int)$party->getId(),
            );
        }

        usort($editions, static fn(PartyEditionDto $a, PartyEditionDto $b): int => $a->year <=> $b->year);

        return $editions;
    }

    private function extractDomain(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        return is_string($host) && $host !== '' ? $host : $url;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES);
    }
}
