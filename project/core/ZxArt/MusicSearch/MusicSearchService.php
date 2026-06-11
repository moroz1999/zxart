<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch;

use ApiQueriesManager;
use authorElement;
use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use structureManager;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\Dto\MusicSearchResult;
use ZxArt\PictureSearch\Dto\LocationDto;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class MusicSearchService
{
    public const int ELEMENTS_ON_PAGE = 60;
    private const int MIN_YEAR = 1970;
    private const string MUSIC_EXPORT_TYPE = 'zxMusic';
    private const string AUTHORS_EXPORT_TYPE = 'author';

    public function __construct(
        private ApiQueriesManager $apiQueriesManager,
        private TunesTransformer $tunesTransformer,
        private AuthorListTransformer $authorListTransformer,
        private LanguagesManager $languagesManager,
        private structureManager $structureManager,
        private controller $controller,
        private Connection $db,
    ) {
    }

    public function search(MusicSearchQuery $query): MusicSearchResult
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

        $elements = array_filter(
            (array)($queryResult[$exportType] ?? []),
            static fn(mixed $element): bool => is_object($element),
        );
        $totalAmount = (int)($queryResult['totalAmount'] ?? 0);

        return new MusicSearchResult(
            totalAmount: $totalAmount,
            resultsType: $query->resultsType,
            tunes: $this->transformTunes($elements),
            authors: $this->transformAuthors($elements),
            formats: $this->getMusicFormats(),
            apiUrl: $this->buildApiUrl($filtrationParameters, $exportType, $query),
            zipUrl: $this->buildZipUrl($filtrationParameters, $exportType),
        );
    }

    /**
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
     * @return string[]
     */
    public function getMusicFormats(): array
    {
        /** @var string[] $formats */
        $formats = $this->db->table('module_zxmusic')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type', 'asc')
            ->pluck('type');
        return $formats;
    }

    /**
     * Ports the music branch of the legacy detailedSearchElement::getQueryParameters().
     *
     * @return array<string, string|int|float|bool|array<array-key, string|int|float>>
     */
    private function buildFiltrationParameters(MusicSearchQuery $query): array
    {
        $parameters = [];
        if ($query->titleWord !== null) {
            $parameters['zxMusicTitleSearch'] = $query->titleWord;
        }

        $years = $this->buildYearRange($query->startYear, $query->endYear);
        if ($years !== []) {
            $parameters['zxMusicYear'] = $years;
        }

        if ($query->minPartyPlace !== null) {
            $parameters['zxMusicMinPartyPlace'] = $query->minPartyPlace;
        }
        if ($query->minRating !== null) {
            $parameters['zxMusicMinRating'] = $query->minRating;
        }
        if ($query->formatGroup !== null) {
            $parameters['zxMusicFormatGroup'] = $query->formatGroup;
        }
        if ($query->format !== null) {
            $parameters['zxMusicFormat'] = $query->format;
        }
        if ($query->realtimeOnly === true) {
            $parameters['zxMusicCompo'] = ['realtime', 'realtimeay', 'realtimebeeper'];
        }
        if ($query->tagsInclude !== []) {
            $parameters['zxMusicTagsInclude'] = $query->tagsInclude;
        }
        if ($query->tagsExclude !== []) {
            $parameters['zxMusicTagsExclude'] = $query->tagsExclude;
        }

        if ($parameters === []) {
            $parameters['zxMusicAll'] = true;
        }

        if ($query->authorCountryIds !== []) {
            $parameters['authorCountry'] = $query->authorCountryIds;
        }
        if ($query->authorCityIds !== []) {
            $parameters['authorCity'] = $query->authorCityIds;
        }
        if ($query->resultsType === PictureSearchResultsType::Authors) {
            $parameters['authorOfItemType'] = 'authorMusic';
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
        return self::MUSIC_EXPORT_TYPE;
    }

    /**
     * @param object[] $elements
     * @return \ZxArt\Tunes\Dto\TuneDto[]
     */
    private function transformTunes(array $elements): array
    {
        $tunes = [];
        foreach ($elements as $element) {
            if ($element instanceof zxMusicElement) {
                $tunes[] = $this->tunesTransformer->toDto($element);
            }
        }
        return $tunes;
    }

    /**
     * @param object[] $elements
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
     * @param array<string, string|int|float|bool|array<array-key, string|int|float>> $filtrationParameters
     */
    private function buildApiUrl(array $filtrationParameters, string $exportType, MusicSearchQuery $query): string
    {
        $url = $this->controller->baseURL . 'api/';
        $url .= 'types:' . $exportType . '/';
        $url .= 'export:' . $exportType . '/';
        $url .= 'language:' . (string)$this->languagesManager->getCurrentLanguageCode() . '/';
        $url .= 'start:' . $query->start . '/';
        $url .= 'limit:' . $query->limit . '/';
        $url .= 'order:' . $query->sortParameter->value . ',' . $query->sortOrder->value . '/';
        $url .= $this->generateQueryString($filtrationParameters);
        return $url;
    }

    /**
     * @param array<string, string|int|float|bool|array<array-key, string|int|float>> $filtrationParameters
     */
    private function buildZipUrl(array $filtrationParameters, string $exportType): string
    {
        $url = $this->controller->baseURL . 'zipItems/';
        $url .= 'export:' . $exportType . '/';
        $url .= 'language:' . (string)$this->languagesManager->getCurrentLanguageCode() . '/';
        $url .= 'structure:authors/';
        $url .= $this->generateQueryString($filtrationParameters);
        return $url;
    }

    /**
     * @param array<string, string|int|float|bool|array<array-key, string|int|float>> $filtrationParameters
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
                $string .= $name . '=' . (string)$value . ';';
            }
        }
        return $string;
    }
}
