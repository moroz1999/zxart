<?php

declare(strict_types=1);

namespace ZxArt\PictureSearch;

use ApiQueriesManager;
use authorElement;
use controller;
use LanguagesManager;
use structureManager;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\PictureSearch\Dto\LocationDto;
use ZxArt\PictureSearch\Dto\PictureSearchQuery;
use ZxArt\PictureSearch\Dto\PictureSearchResult;
use ZxArt\Pictures\PicturesTransformer;
use zxPictureElement;

readonly class PictureSearchService
{
    public const int ELEMENTS_ON_PAGE = 60;
    private const int MIN_YEAR = 1970;
    private const string PICTURES_EXPORT_TYPE = 'zxPicture';
    private const string AUTHORS_EXPORT_TYPE = 'author';

    public function __construct(
        private ApiQueriesManager $apiQueriesManager,
        private PicturesTransformer $picturesTransformer,
        private AuthorListTransformer $authorListTransformer,
        private LanguagesManager $languagesManager,
        private structureManager $structureManager,
        private controller $controller,
    ) {
    }

    public function search(PictureSearchQuery $query): PictureSearchResult
    {
        $filtrationParameters = $this->buildFiltrationParameters($query);
        $exportType = $this->resolveExportType($query->resultsType);
        $order = [$query->sortParameter->value => $query->sortOrder->value];

        $apiQuery = $this->apiQueriesManager->getQuery();
        $apiQuery->setFiltrationParameters($filtrationParameters);
        $apiQuery->setExportType($exportType);
        $apiQuery->setOrder($order);
        $apiQuery->setStart($query->start);
        $apiQuery->setLimit($query->limit);
        $queryResult = (array)$apiQuery->getQueryResult();

        $elements = (array)($queryResult[$exportType] ?? []);
        $totalAmount = (int)($queryResult['totalAmount'] ?? 0);

        return new PictureSearchResult(
            totalAmount: $totalAmount,
            resultsType: $query->resultsType,
            pictures: $this->transformPictures($elements),
            authors: $this->transformAuthors($elements),
            apiUrl: $this->buildApiUrl($filtrationParameters, $exportType, $query),
            zipUrl: $this->buildZipUrl($filtrationParameters, $exportType),
        );
    }

    /**
     * Resolves country/city titles for restoring filter chips from URL ids.
     *
     * @param int[] $ids
     * @return LocationDto[]
     */
    public function resolveLocations(array $ids): array
    {
        $locations = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id, null, true);
            if ($element === null) {
                continue;
            }
            $locations[] = new LocationDto(
                id: (int)$element->id,
                title: html_entity_decode($element->title, ENT_QUOTES),
            );
        }
        return $locations;
    }

    /**
     * Ports the graphics branch of the legacy detailedSearchElement::getQueryParameters().
     *
     * @return array<string, mixed>
     */
    private function buildFiltrationParameters(PictureSearchQuery $query): array
    {
        $parameters = [];
        if ($query->titleWord !== null) {
            $parameters['zxPictureTitleSearch'] = $query->titleWord;
        }

        $years = $this->buildYearRange($query->startYear, $query->endYear);
        if ($years !== []) {
            $parameters['zxPictureYear'] = $years;
        }

        if ($query->minPartyPlace !== null) {
            $parameters['zxPictureMinPartyPlace'] = $query->minPartyPlace;
        }
        if ($query->minRating !== null) {
            $parameters['zxPictureMinRating'] = $query->minRating;
        }
        if ($query->pictureType !== null) {
            $parameters['zxPictureType'] = $query->pictureType;
        }
        if ($query->realtimeOnly === true) {
            $parameters['zxPictureCompo'] = ['realtime', 'realtimep'];
        }
        if ($query->inspirationOnly === true) {
            $parameters['zxPictureInspiration'] = true;
        }
        if ($query->stagesOnly === true) {
            $parameters['zxPictureStages'] = true;
        }
        if ($query->tagsInclude !== []) {
            $parameters['zxPictureTagsInclude'] = $query->tagsInclude;
        }
        if ($query->tagsExclude !== []) {
            $parameters['zxPictureTagsExclude'] = $query->tagsExclude;
        }

        // Legacy behavior: the "match all" fallback only considers picture filters,
        // so author location filters are appended after the check.
        if ($parameters === []) {
            $parameters['zxPictureAll'] = true;
        }

        if ($query->authorCountryIds !== []) {
            $parameters['authorCountry'] = $query->authorCountryIds;
        }
        if ($query->authorCityIds !== []) {
            $parameters['authorCity'] = $query->authorCityIds;
        }
        if ($query->resultsType === PictureSearchResultsType::Authors) {
            $parameters['authorOfItemType'] = 'authorPicture';
        }

        return $parameters;
    }

    /**
     * @return int[]
     */
    private function buildYearRange(?int $startYear, ?int $endYear): array
    {
        $start = $startYear ?? 0;
        $end = $endYear ?? 0;
        if ($start > 0 && $end === 0) {
            $end = (int)date('Y');
        }
        if ($end > 0 && $start === 0) {
            $start = self::MIN_YEAR;
        }
        if ($start <= 0 || $end <= 0) {
            return [];
        }
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }
        return range($start, $end);
    }

    private function resolveExportType(PictureSearchResultsType $resultsType): string
    {
        if ($resultsType === PictureSearchResultsType::Authors) {
            return self::AUTHORS_EXPORT_TYPE;
        }
        return self::PICTURES_EXPORT_TYPE;
    }

    /**
     * @param mixed[] $elements
     * @return \ZxArt\Pictures\Dto\PictureDto[]
     */
    private function transformPictures(array $elements): array
    {
        $pictures = [];
        foreach ($elements as $element) {
            if ($element instanceof zxPictureElement) {
                $pictures[] = $this->picturesTransformer->toDto($element);
            }
        }
        return $pictures;
    }

    /**
     * @param mixed[] $elements
     * @return \ZxArt\AuthorList\Dto\AuthorListItemDto[]
     */
    private function transformAuthors(array $elements): array
    {
        $authors = [];
        foreach ($elements as $element) {
            if ($element instanceof authorElement) {
                $authors[] = $this->authorListTransformer->authorToDto($element);
            }
        }
        return $authors;
    }

    /**
     * Ports the legacy detailedSearchElement::getApiUrl().
     *
     * @param array<string, mixed> $filtrationParameters
     */
    private function buildApiUrl(array $filtrationParameters, string $exportType, PictureSearchQuery $query): string
    {
        $url = $this->controller->baseURL . 'api/';
        $url .= 'types:' . $exportType . '/';
        $url .= 'export:' . $exportType . '/';
        $url .= 'language:' . $this->languagesManager->getCurrentLanguageCode() . '/';
        $url .= 'start:' . $query->start . '/';
        $url .= 'limit:' . $query->limit . '/';
        $url .= 'order:' . $query->sortParameter->value . ',' . $query->sortOrder->value . '/';
        $url .= $this->generateQueryString($filtrationParameters);
        return $url;
    }

    /**
     * Ports the legacy detailedSearchElement::getSaveUrl().
     *
     * @param array<string, mixed> $filtrationParameters
     */
    private function buildZipUrl(array $filtrationParameters, string $exportType): string
    {
        $url = $this->controller->baseURL . 'zipItems/';
        $url .= 'export:' . $exportType . '/';
        $url .= 'language:' . $this->languagesManager->getCurrentLanguageCode() . '/';
        $url .= 'structure:authors/';
        $url .= $this->generateQueryString($filtrationParameters);
        return $url;
    }

    /**
     * @param array<string, mixed> $filtrationParameters
     */
    private function generateQueryString(array $filtrationParameters): string
    {
        if ($filtrationParameters === []) {
            return '';
        }
        $string = 'filter:';
        foreach ($filtrationParameters as $name => $value) {
            if (is_array($value)) {
                $string .= $name . '=' . implode(',', $value) . ';';
            } elseif (is_bool($value)) {
                $string .= $name . '=' . ($value === true ? '1' : '0') . ';';
            } else {
                $string .= $name . '=' . $value . ';';
            }
        }
        return $string;
    }
}
