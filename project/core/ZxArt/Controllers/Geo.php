<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Override;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\AuthorList\AuthorListService;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\Geo\Dto\GeoPartyListItemDto;
use ZxArt\Geo\GeoService;
use ZxArt\Geo\Rest\GeoMapRestDto;
use ZxArt\Geo\Rest\GeoPartyListItemRestDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\GroupListService;
use ZxArt\GroupList\Rest\GroupListItemRestDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\SortingParams;

class Geo extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly GeoService $geoService,
        private readonly AuthorListService $authorListService,
        private readonly GroupListService $groupListService,
        private readonly ObjectMapper $objectMapper,
    ) {
        parent::__construct($controller, $logger);
    }

    #[Override]
    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);
    }

    #[Override]
    public function execute($controller): void
    {
        $action = $this->getStringParameter('action', 'map');

        try {
            match ($action) {
                'map' => $this->handleMap(),
                'authors' => $this->handleAuthors(),
                'groups' => $this->handleGroups(),
                'parties' => $this->handleParties(),
                default => $this->assignError('Unknown action', 400),
            };
        } catch (Throwable $e) {
            $this->logThrowable('Geo::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleMap(): void
    {
        $this->assignSuccess($this->objectMapper->map($this->geoService->getMap(), GeoMapRestDto::class));
    }

    private function handleAuthors(): void
    {
        $sorting = SortingParams::fromRequest(
            $this->getStringParameter('sorting', 'title,asc'),
            ['title', 'graphicsRating', 'musicRating', 'id']
        );
        $result = $this->authorListService->getPaged(
            sorting: $sorting,
            start: $this->getStart(),
            limit: $this->getLimit(),
            search: $this->getSearch(),
            countryId: $this->getCountryId(),
            cityId: $this->getCityId(),
            types: [EntityType::Author],
            north: $this->getNorth(),
            south: $this->getSouth(),
            east: $this->getEast(),
            west: $this->getWest(),
        );

        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                fn(AuthorListItemDto $dto) => $this->objectMapper->map($dto, AuthorListItemRestDto::class),
                $result['items'],
            ),
        ]);
    }

    private function handleGroups(): void
    {
        $sorting = SortingParams::fromRequest($this->getStringParameter('sorting', 'title,asc'), ['title', 'id']);
        $result = $this->groupListService->getPaged(
            sorting: $sorting,
            start: $this->getStart(),
            limit: $this->getLimit(),
            search: $this->getSearch(),
            countryId: $this->getCountryId(),
            cityId: $this->getCityId(),
            types: [EntityType::Group],
            north: $this->getNorth(),
            south: $this->getSouth(),
            east: $this->getEast(),
            west: $this->getWest(),
        );

        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                fn(GroupListItemDto $dto) => $this->objectMapper->map($dto, GroupListItemRestDto::class),
                $result['items'],
            ),
        ]);
    }

    private function handleParties(): void
    {
        $sortingRaw = $this->getStringParameter('sorting', 'title,asc');
        $sorting = SortingParams::fromRequest($sortingRaw, ['title', 'id']);
        $result = $this->geoService->getPagedParties(
            start: $this->getStart(),
            limit: $this->getLimit(),
            sortColumn: $sorting->column,
            sortDirection: $sorting->direction->value,
            countryId: $this->getCountryId(),
            cityId: $this->getCityId(),
            north: $this->getNorth(),
            south: $this->getSouth(),
            east: $this->getEast(),
            west: $this->getWest(),
            search: $this->getSearch(),
        );

        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                fn(GeoPartyListItemDto $dto) => $this->objectMapper->map($dto, GeoPartyListItemRestDto::class),
                $result['items'],
            ),
        ]);
    }

    private function getStart(): int
    {
        return max(0, (int)($this->getParameter('start') ?: 0));
    }

    private function getLimit(): int
    {
        $limit = (int)($this->getParameter('limit') ?: 50);

        return min(100, max(1, $limit));
    }

    private function getSearch(): ?string
    {
        $search = (string)($this->getParameter('search') ?: '');

        return $search !== '' ? $search : null;
    }

    private function getCountryId(): ?int
    {
        return $this->getParameter('countryId') !== false ? (int)$this->getParameter('countryId') : null;
    }

    private function getCityId(): ?int
    {
        return $this->getParameter('cityId') !== false ? (int)$this->getParameter('cityId') : null;
    }

    private function getNorth(): ?float
    {
        return $this->getFloatParameter('north');
    }

    private function getSouth(): ?float
    {
        return $this->getFloatParameter('south');
    }

    private function getEast(): ?float
    {
        return $this->getFloatParameter('east');
    }

    private function getWest(): ?float
    {
        return $this->getFloatParameter('west');
    }

    private function getFloatParameter(string $name): ?float
    {
        $value = (string)($this->getParameter($name) ?: '');
        if ($value === '') {
            return null;
        }

        return (float)$value;
    }

    private function getStringParameter(string $name, string $default): string
    {
        $value = (string)($this->getParameter($name) ?: '');

        return $value !== '' ? $value : $default;
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

    #[Override]
    public function getUrlName(): string
    {
        return '';
    }
}
