<?php

declare(strict_types=1);

namespace ZxArt\MusicSearch;

use authorElement;
use controller;
use Illuminate\Database\Connection;
use LanguagesManager;
use structureManager;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\Dto\MusicSearchResult;
use ZxArt\MusicSearch\Repositories\MusicSearchRepository;
use ZxArt\PictureSearch\Dto\LocationDto;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\Tunes\Services\TunesManager;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;

readonly class MusicSearchService
{
    public const int ELEMENTS_ON_PAGE = 60;
    private const int MIN_YEAR = 1970;
    private const string MUSIC_EXPORT_TYPE = 'zxMusic';
    private const string AUTHORS_EXPORT_TYPE = 'author';

    public function __construct(
        private MusicSearchRepository $musicSearchRepository,
        private TunesManager $tunesManager,
        private AuthorsService $authorsService,
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
        $order = [$query->sortParameter->value => $query->sortOrder->value];

        if ($query->resultsType === PictureSearchResultsType::Authors) {
            $authorsQuery = $this->musicSearchRepository->buildAuthorsQuery($query);
            $totalAmount = (clone $authorsQuery)->count();
            /** @var \structureElement[] $authorElements */
            $authorElements = $this->authorsService->getElementsByQuery($authorsQuery, $order, $query->start, $query->limit);
            $tunes = [];
            $authors = $this->transformAuthors($authorElements);
        } else {
            $tunesQuery = $this->musicSearchRepository->buildTunesQuery($query);
            $totalAmount = (clone $tunesQuery)->count();
            /** @var \structureElement[] $tuneElements */
            $tuneElements = $this->tunesManager->getElementsByQuery($tunesQuery, $order, $query->start, $query->limit);
            $tunes = $this->transformTunes($tuneElements);
            $authors = [];
        }

        $filtrationParameters = $this->buildFiltrationParameters($query);
        $exportType = $this->resolveExportType($query->resultsType);

        return new MusicSearchResult(
            totalAmount: $totalAmount,
            resultsType: $query->resultsType,
            tunes: $tunes,
            authors: $authors,
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
