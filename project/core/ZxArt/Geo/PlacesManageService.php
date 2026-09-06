<?php

declare(strict_types=1);

namespace ZxArt\Geo;

use cityElement;
use countryElement;
use LanguagesManager;
use linksManager;
use structureElement;
use structureManager;
use ZxArt\Geo\Dto\CitySaveDto;
use ZxArt\Geo\Dto\CountrySaveDto;
use ZxArt\Geo\Dto\ManageCityDto;
use ZxArt\Geo\Dto\ManageCountryDto;
use ZxArt\Geo\Exception\GeoException;
use ZxArt\Geo\Repositories\GeoManageRepository;
use ZxArt\LinkTypes;
use ZxArt\Shared\StructureType;

/**
 * Creating, editing, moving and removing the countries and cities of the geo
 * section.
 *
 * Countries hang under the single `countries` container and carry a second,
 * `countries` link from every geo section — that projection is what a section
 * lists, and the admin section form rebuilds it wholesale. Nothing recomputes
 * it on its own, so creating a country establishes those links here.
 *
 * Cities are ordinary children of their country, and `countryId` states which
 * one on a creation and on an update alike: a city without a country would be
 * unreachable and would never appear in a section.
 */
final class PlacesManageService
{
    private const int MAX_TITLE_LENGTH = 255;
    private const float MAX_LATITUDE = 90.0;
    private const float MAX_LONGITUDE = 180.0;

    /** @var array<int, int>|null */
    private ?array $cityCountries = null;
    /** @var array<int, int>|null */
    private ?array $placeUsages = null;

    public function __construct(
        private readonly GeoManageRepository $repository,
        private readonly structureManager $structureManager,
        private readonly linksManager $linksManager,
        private readonly LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return list<ManageCountryDto> in the current language's alphabetical order
     */
    public function getCountries(): array
    {
        $places = $this->groupPlaceRows($this->repository->getCountryRows());
        $usages = $this->getPlaceUsages();
        $currentLanguageId = (int)$this->languagesManager->getCurrentLanguageId();
        $cityCounts = array_count_values(array_values($this->getCityCountries()));

        $countries = [];
        foreach ($places as $id => $place) {
            $countries[] = new ManageCountryDto(
                id: $id,
                title: $place['titles'][$currentLanguageId] ?? '',
                titles: $place['titles'],
                latitude: $place['latitude'],
                longitude: $place['longitude'],
                usages: $usages[$id] ?? 0,
                cities: $cityCounts[$id] ?? 0,
            );
        }

        usort($countries, static fn(ManageCountryDto $a, ManageCountryDto $b): int => strcasecmp($a->title, $b->title));

        return $countries;
    }

    /**
     * @return list<ManageCityDto> in the current language's alphabetical order
     */
    public function getCities(): array
    {
        $places = $this->groupPlaceRows($this->repository->getCityRows());
        $usages = $this->getPlaceUsages();
        $countryIds = $this->getCityCountries();
        $currentLanguageId = (int)$this->languagesManager->getCurrentLanguageId();

        $cities = [];
        foreach ($places as $id => $place) {
            $countryId = $countryIds[$id] ?? 0;
            if ($countryId === 0) {
                continue;
            }
            $cities[] = new ManageCityDto(
                id: $id,
                countryId: $countryId,
                title: $place['titles'][$currentLanguageId] ?? '',
                titles: $place['titles'],
                latitude: $place['latitude'],
                longitude: $place['longitude'],
                usages: $usages[$id] ?? 0,
            );
        }

        usort($cities, static fn(ManageCityDto $a, ManageCityDto $b): int => strcasecmp($a->title, $b->title));

        return $cities;
    }

    /**
     * @throws GeoException
     */
    public function createCountry(CountrySaveDto $request): int
    {
        $titles = $this->validateTitles($request->titles);
        $this->validateCoordinates($request->latitude, $request->longitude);

        // the container lives under the admin root, outside the public URL tree,
        // so it is loaded by id rather than resolved through a path
        $container = $this->structureManager->getElementByMarker(
            StructureType::Countries->value,
            null,
            true,
        );
        if (!$container instanceof structureElement) {
            throw new GeoException('The countries container is not available', 500);
        }

        $element = $this->structureManager->createElement(
            StructureType::Country->value,
            'show',
            $container->getId(),
        );
        if (!$element instanceof countryElement) {
            throw new GeoException('Country could not be created', 500);
        }

        // persistElementData has already cleared the container's cache through
        // persistStructureLinks; only the section projection is written here
        $this->applyPlace($element, $titles, $request->latitude, $request->longitude);
        $this->linkToSections($element->getId());
        $this->resetCache();

        return $element->getId();
    }

    /**
     * @throws GeoException
     */
    public function updateCountry(CountrySaveDto $request): void
    {
        $id = $request->id
            ?? throw new GeoException('Missing required field: id', 400);
        $titles = $this->validateTitles($request->titles);
        $this->validateCoordinates($request->latitude, $request->longitude);

        $this->applyPlace($this->getCountry($id), $titles, $request->latitude, $request->longitude);
    }

    /**
     * A country still holding cities or named by an author, a group or a party
     * is never removed on the way past: deletion cascades down its links.
     *
     * @throws GeoException
     */
    public function deleteCountry(int $id): void
    {
        $element = $this->getCountry($id);

        $this->assertUnused($id);
        $hasCities = in_array($id, $this->getCityCountries(), true);
        if ($hasCities) {
            throw new GeoException('Country still has cities and cannot be deleted', 409);
        }

        // deleteElementData drops every link and clears the parents' caches
        $element->deleteElementData();
        $this->resetCache();
    }

    /**
     * @throws GeoException
     */
    public function createCity(CitySaveDto $request): int
    {
        $countryId = $request->countryId
            ?? throw new GeoException('Missing required field: countryId', 400);
        $titles = $this->validateTitles($request->titles);
        $this->validateCoordinates($request->latitude, $request->longitude);
        $country = $this->getCountry($countryId);

        $element = $this->structureManager->createElement(
            StructureType::City->value,
            'show',
            $country->getId(),
        );
        if (!$element instanceof cityElement) {
            throw new GeoException('City could not be created', 500);
        }

        $this->applyPlace($element, $titles, $request->latitude, $request->longitude);
        $this->resetCache();

        return $element->getId();
    }

    /**
     * @throws GeoException
     */
    public function updateCity(CitySaveDto $request): void
    {
        $id = $request->id
            ?? throw new GeoException('Missing required field: id', 400);
        $countryId = $request->countryId
            ?? throw new GeoException('Missing required field: countryId', 400);
        $titles = $this->validateTitles($request->titles);
        $this->validateCoordinates($request->latitude, $request->longitude);
        $element = $this->getCity($id);

        $this->applyPlace($element, $titles, $request->latitude, $request->longitude);
        $this->moveCity($id, $countryId);
        $this->resetCache();
    }

    /**
     * @throws GeoException
     */
    public function deleteCity(int $id): void
    {
        $element = $this->getCity($id);
        $this->assertUnused($id);

        // deleteElementData drops every link and clears the parents' caches
        $element->deleteElementData();
        $this->resetCache();
    }

    /**
     * @throws GeoException
     */
    private function getCountry(int $id): countryElement
    {
        $element = $this->structureManager->getElementById($id);

        return $element instanceof countryElement
            ? $element
            : throw new GeoException('Country not found', 404);
    }

    /**
     * @throws GeoException
     */
    private function getCity(int $id): cityElement
    {
        $element = $this->structureManager->getElementById($id);

        return $element instanceof cityElement
            ? $element
            : throw new GeoException('City not found', 404);
    }

    /**
     * @throws GeoException
     */
    private function assertUnused(int $placeId): void
    {
        $usages = $this->getPlaceUsages()[$placeId] ?? 0;
        if ($usages > 0) {
            throw new GeoException(
                'Place is still named by ' . $usages . ' entity(ies) and cannot be deleted',
                409,
            );
        }
    }

    /**
     * @param array<int, string> $titles
     * @throws GeoException
     */
    private function applyPlace(
        cityElement|countryElement $element,
        array $titles,
        float $latitude,
        float $longitude,
    ): void {
        $externalData = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
        foreach ($titles as $languageId => $title) {
            $externalData[$languageId] = ['title' => $title];
        }

        $isValid = $element->importExternalData($externalData, ['title', 'latitude', 'longitude']);
        if ($isValid !== true) {
            throw new GeoException('Place values are not accepted', 422);
        }

        $element->structureName = $this->getPrimaryTitle($titles);
        $element->persistElementData();
    }

    /**
     * Re-links the city under another country. A city holds no other link, so
     * the move is the whole of it.
     *
     * @throws GeoException
     */
    private function moveCity(int $cityId, int $countryId): void
    {
        $currentCountryId = $this->getCityCountries()[$cityId] ?? null;
        if ($currentCountryId === $countryId) {
            return;
        }

        $country = $this->getCountry($countryId);
        if ($currentCountryId !== null) {
            $this->linksManager->unLinkElements($currentCountryId, $cityId, LinkTypes::STRUCTURE->value);
        }
        $this->linksManager->linkElements($country->getId(), $cityId, LinkTypes::STRUCTURE->value);

        $this->clearElementCaches($cityId, $currentCountryId ?? 0, $country->getId());
    }

    /**
     * Every geo section lists the countries linked to it, so a new country has
     * to reach all of them — the sections are one per interface language.
     */
    private function linkToSections(int $countryId): void
    {
        $sections = $this->structureManager->getElementsByType(StructureType::CountriesList->value);
        foreach ($sections as $section) {
            $this->linksManager->linkElements(
                $section->getId(),
                $countryId,
                LinkTypes::COUNTRIES->value,
            );
        }
    }

    /**
     * A link written straight through the links manager leaves the parent's
     * cached copy behind — only `persistStructureLinks()` clears it — so every
     * element whose children changed is dropped from the cache here.
     */
    private function clearElementCaches(int ...$elementIds): void
    {
        foreach (array_unique($elementIds) as $elementId) {
            if ($elementId > 0) {
                $this->structureManager->clearElementCache($elementId);
            }
        }
    }

    /**
     * @return array<int, int> cityId => countryId
     */
    private function getCityCountries(): array
    {
        return $this->cityCountries ??= $this->repository->getCityCountries();
    }

    /**
     * @return array<int, int> placeId => how many entities name it
     */
    private function getPlaceUsages(): array
    {
        return $this->placeUsages ??= $this->repository->getPlaceUsages();
    }

    /**
     * A write answers with the refreshed lists in the same request, so what was
     * read before it must not be reused after it.
     */
    private function resetCache(): void
    {
        $this->cityCountries = null;
        $this->placeUsages = null;
    }

    /**
     * @param list<array{id: int, languageId: int, title: string, latitude: float, longitude: float}> $rows
     * @return array<int, array{titles: array<int, string>, latitude: float, longitude: float}>
     */
    private function groupPlaceRows(array $rows): array
    {
        $places = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            if (!isset($places[$id])) {
                $places[$id] = [
                    'titles' => [],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                ];
            }
            $places[$id]['titles'][$row['languageId']] = html_entity_decode(
                $row['title'],
                ENT_QUOTES | ENT_HTML5,
                'UTF-8',
            );
        }

        return $places;
    }

    /**
     * The title the element's own name is built from. Every language carries
     * one by then, so the first is as good as any — the structure name is only
     * ever a URL slug.
     *
     * @param array<int, string> $titles
     */
    private function getPrimaryTitle(array $titles): string
    {
        foreach ($titles as $title) {
            return $title;
        }

        return '';
    }

    /**
     * Every interface language needs a title: a place without one shows a blank
     * label to that whole audience.
     *
     * @param array<int, string> $titles
     * @return array<int, string>
     * @throws GeoException
     */
    private function validateTitles(array $titles): array
    {
        $validated = [];
        foreach ($this->languagesManager->getLanguagesIdList() as $languageId) {
            $title = trim($titles[$languageId] ?? '');
            if ($title === '') {
                throw new GeoException('Title is required for language ' . $languageId, 422);
            }
            if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
                throw new GeoException('Title is longer than ' . self::MAX_TITLE_LENGTH . ' characters', 422);
            }
            $validated[$languageId] = $title;
        }

        return $validated;
    }

    /**
     * @throws GeoException
     */
    private function validateCoordinates(float $latitude, float $longitude): void
    {
        if (abs($latitude) > self::MAX_LATITUDE) {
            throw new GeoException('Latitude must be between -90 and 90', 422);
        }
        if (abs($longitude) > self::MAX_LONGITUDE) {
            throw new GeoException('Longitude must be between -180 and 180', 422);
        }
    }
}
