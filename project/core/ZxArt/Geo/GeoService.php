<?php

declare(strict_types=1);

namespace ZxArt\Geo;

use cityElement;
use countryElement;
use partyElement;
use structureManager;
use ZxArt\Geo\Dto\GeoCityDto;
use ZxArt\Geo\Dto\GeoCountersDto;
use ZxArt\Geo\Dto\GeoCountryDto;
use ZxArt\Geo\Dto\GeoMapDto;
use ZxArt\Geo\Dto\GeoPartyListItemDto;
use ZxArt\Geo\Repositories\GeoRepository;
use ZxArt\Shared\SortDirection;

readonly class GeoService
{
    public function __construct(
        private GeoRepository $repository,
        private structureManager $structureManager,
    ) {
    }

    public function getMap(): GeoMapDto
    {
        $countries = $this->repository->findCountries();
        $cities = $this->repository->findCities();
        $authorCountries = $this->repository->countAuthorsByCountry();
        $authorCities = $this->repository->countAuthorsByCity();
        $groupCountries = $this->repository->countGroupsByCountry();
        $groupCities = $this->repository->countGroupsByCity();
        $partyCountries = $this->repository->countPartiesByCountry();
        $partyCities = $this->repository->countPartiesByCity();

        $countryDtos = [];
        $totalCounters = new GeoCountersDto(0, 0, 0);

        foreach ($countries as $countryId => $country) {
            $cityDtos = $this->buildCityDtos($cities, $countryId, $authorCities, $groupCities, $partyCities);
            $countryCounters = new GeoCountersDto(
                authors: $authorCountries[$countryId] ?? 0,
                groups: $groupCountries[$countryId] ?? 0,
                parties: $partyCountries[$countryId] ?? 0,
            );
            $totalCounters = $this->addCounters($totalCounters, $countryCounters);

            if ($this->isEmpty($countryCounters) && $cityDtos === []) {
                continue;
            }

            $countryElement = $this->structureManager->getElementById($countryId);
            $countryDtos[] = new GeoCountryDto(
                id: $countryId,
                title: $country['title'],
                url: $countryElement instanceof countryElement ? ($countryElement->getUrl() ?? '') : '',
                latitude: $country['latitude'],
                longitude: $country['longitude'],
                counters: $countryCounters,
                cities: $cityDtos,
            );
        }

        return new GeoMapDto($countryDtos, $totalCounters);
    }

    /**
     * @return array{total: int, items: GeoPartyListItemDto[]}
     */
    public function getPagedParties(
        int $start,
        int $limit,
        string $sortColumn,
        string $sortDirection,
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
        ?string $search,
    ): array {
        $direction = SortDirection::tryFrom($sortDirection) ?? SortDirection::ASC;
        $column = in_array($sortColumn, ['title', 'id'], true) ? $sortColumn : 'title';
        $total = $this->repository->countParties($countryId, $cityId, $north, $south, $east, $west, $search);
        $ids = $this->repository->findPartyIds($start, $limit, $column, $direction, $countryId, $cityId, $north, $south, $east, $west, $search);

        $items = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof partyElement) {
                $items[] = $this->buildPartyListItem($element);
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    /**
     * @param array<int, array{id: int, countryId: int, title: string, latitude: float, longitude: float}> $cities
     * @param array<int, int> $authorCities
     * @param array<int, int> $groupCities
     * @param array<int, int> $partyCities
     *
     * @return GeoCityDto[]
     */
    private function buildCityDtos(
        array $cities,
        int $countryId,
        array $authorCities,
        array $groupCities,
        array $partyCities,
    ): array {
        $cityDtos = [];
        foreach ($cities as $cityId => $city) {
            if ($city['countryId'] !== $countryId) {
                continue;
            }

            $counters = new GeoCountersDto(
                authors: $authorCities[$cityId] ?? 0,
                groups: $groupCities[$cityId] ?? 0,
                parties: $partyCities[$cityId] ?? 0,
            );

            if ($this->isEmpty($counters)) {
                continue;
            }

            $cityElement = $this->structureManager->getElementById($cityId);
            $cityDtos[] = new GeoCityDto(
                id: $cityId,
                countryId: $countryId,
                title: $city['title'],
                url: $cityElement instanceof cityElement ? ($cityElement->getUrl() ?? '') : '',
                latitude: $city['latitude'],
                longitude: $city['longitude'],
                counters: $counters,
            );
        }

        return $cityDtos;
    }

    private function addCounters(GeoCountersDto $left, GeoCountersDto $right): GeoCountersDto
    {
        return new GeoCountersDto(
            authors: $left->authors + $right->authors,
            groups: $left->groups + $right->groups,
            parties: $left->parties + $right->parties,
        );
    }

    private function isEmpty(GeoCountersDto $counters): bool
    {
        return $counters->authors === 0 && $counters->groups === 0 && $counters->parties === 0;
    }

    private function buildPartyListItem(partyElement $element): GeoPartyListItemDto
    {
        $countryElement = $element->getCountryElement();
        $cityElement = $element->getCityElement();

        return new GeoPartyListItemDto(
            id: (int)$element->id,
            title: html_entity_decode($element->title, ENT_QUOTES),
            url: $element->getUrl() ?? '',
            countryId: $countryElement !== null ? (int)$countryElement->id : null,
            countryTitle: $countryElement !== null ? html_entity_decode($countryElement->title, ENT_QUOTES) : null,
            countryUrl: $countryElement?->getUrl(),
            cityId: $cityElement !== null ? (int)$cityElement->id : null,
            cityTitle: $cityElement !== null ? html_entity_decode($cityElement->title, ENT_QUOTES) : null,
            cityUrl: $cityElement?->getUrl(),
            entries: $element->picturesQuantity + $element->tunesQuantity,
        );
    }
}
