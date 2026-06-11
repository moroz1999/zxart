<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use structureManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\MusicSearch\Dto\MusicSearchQuery;
use ZxArt\MusicSearch\MusicSearchService;
use ZxArt\MusicSearch\MusicSearchSort;
use ZxArt\PictureSearch\Dto\LocationDto;
use ZxArt\PictureSearch\PictureSearchOrder;
use ZxArt\PictureSearch\PictureSearchResultsType;
use ZxArt\PictureSearch\Rest\LocationRestDto;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

final class MusicSearch extends LoggedControllerApplication
{
    private const int MAX_LIMIT = 100;

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
        private MusicSearchService $musicSearchService,
        private ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action') !== false ? (string)$this->getParameter('action') : 'search';

        try {
            if ($action === 'search') {
                $this->handleSearch();
            } elseif ($action === 'locations') {
                $this->handleLocations();
            } else {
                $this->assignError('Unknown action', 400);
            }
        } catch (Throwable $e) {
            $this->logThrowable('MusicSearch::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleSearch(): void
    {
        $query = $this->buildQueryFromRequest();
        $result = $this->musicSearchService->search($query);

        $this->assignSuccess([
            'totalAmount' => $result->totalAmount,
            'resultsType' => $result->resultsType->value,
            'tunes' => array_map(
                fn(TuneDto $dto) => $this->objectMapper->map($dto, TuneRestDto::class),
                $result->tunes
            ),
            'authors' => array_map(
                fn(AuthorListItemDto $dto) => $this->objectMapper->map($dto, AuthorListItemRestDto::class),
                $result->authors
            ),
            'formats' => $result->formats,
            'apiUrl' => $result->apiUrl,
            'zipUrl' => $result->zipUrl,
        ]);
    }

    private function handleLocations(): void
    {
        $ids = $this->getIdListParameter('ids');
        if ($ids === []) {
            $this->assignError('ids is required', 400);
            return;
        }
        $locations = $this->musicSearchService->resolveLocations($ids);
        $this->assignSuccess([
            'items' => array_map(
                fn(LocationDto $dto) => $this->objectMapper->map($dto, LocationRestDto::class),
                $locations
            ),
        ]);
    }

    private function buildQueryFromRequest(): MusicSearchQuery
    {
        $resultsType = PictureSearchResultsType::tryFrom((string)$this->getParameter('resultsType'))
            ?? PictureSearchResultsType::Items;
        $sortParameter = MusicSearchSort::tryFrom((string)$this->getParameter('sortParameter'))
            ?? MusicSearchSort::Date;
        $sortOrder = PictureSearchOrder::tryFrom((string)$this->getParameter('sortOrder'))
            ?? PictureSearchOrder::Desc;

        $start = max(0, $this->getIntParameter('start') ?? 0);
        $limit = $this->getIntParameter('limit') ?? MusicSearchService::ELEMENTS_ON_PAGE;
        $limit = min(max(1, $limit), self::MAX_LIMIT);

        return new MusicSearchQuery(
            titleWord: $this->getStringParameter('titleWord'),
            startYear: $this->getIntParameter('startYear'),
            endYear: $this->getIntParameter('endYear'),
            minRating: $this->getFloatParameter('rating'),
            minPartyPlace: $this->getIntParameter('partyPlace'),
            formatGroup: $this->getStringParameter('formatGroup'),
            format: $this->getStringParameter('format'),
            realtimeOnly: $this->getFlagParameter('realtime'),
            tagsInclude: $this->getStringListParameter('tagsInclude'),
            tagsExclude: $this->getStringListParameter('tagsExclude'),
            authorCountryIds: $this->getIdListParameter('authorCountry'),
            authorCityIds: $this->getIdListParameter('authorCity'),
            resultsType: $resultsType,
            sortParameter: $sortParameter,
            sortOrder: $sortOrder,
            start: $start,
            limit: $limit,
        );
    }

    private function getStringParameter(string $name): ?string
    {
        if ($this->getParameter($name) === false) {
            return null;
        }
        $value = trim((string)$this->getParameter($name));
        return $value !== '' ? $value : null;
    }

    private function getIntParameter(string $name): ?int
    {
        $value = $this->getStringParameter($name);
        if ($value === null || !is_numeric($value)) {
            return null;
        }
        return (int)$value;
    }

    private function getFloatParameter(string $name): ?float
    {
        $value = $this->getStringParameter($name);
        if ($value === null || !is_numeric($value)) {
            return null;
        }
        return (float)$value;
    }

    private function getFlagParameter(string $name): bool
    {
        return $this->getStringParameter($name) === '1';
    }

    /**
     * @return string[]
     */
    private function getStringListParameter(string $name): array
    {
        $value = $this->getStringParameter($name);
        if ($value === null) {
            return [];
        }
        $items = [];
        foreach (explode(',', $value) as $item) {
            $item = trim($item);
            if ($item !== '') {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @return int[]
     */
    private function getIdListParameter(string $name): array
    {
        $ids = [];
        foreach ($this->getStringListParameter($name) as $item) {
            if (is_numeric($item)) {
                $id = (int)$item;
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('body', $data);
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }
}
