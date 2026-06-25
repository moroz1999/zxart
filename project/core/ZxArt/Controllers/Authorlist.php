<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\AuthorList\AuthorListService;
use ZxArt\AuthorList\Dto\ActiveAuthorDto;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Dto\FilterOptionDto;
use ZxArt\AuthorList\Rest\ActiveAuthorRestDto;
use ZxArt\AuthorList\Rest\AuthorFilterOptionRestDto;
use ZxArt\AuthorList\Rest\AuthorFilterOptionsRestDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\SortingParams;

class Authorlist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly AuthorListService $authorListService,
        private readonly ObjectMapper $objectMapper,
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
        $action = $this->getParameter('action') ?: '';
        $elementId = (int)($this->getParameter('elementId') ?? 0);

        if ($elementId <= 0) {
            $this->assignError('elementId is required', 400);
            $this->renderer->display();
            return;
        }

        try {
            if ($action === 'filters') {
                $this->handleFilters();
            } elseif ($action === 'active') {
                $this->handleActive();
            } else {
                $this->handleList();
            }
        } catch (Throwable $e) {
            $this->logThrowable('Authorlist::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleList(): void
    {
        $start = (int)($this->getParameter('start') ?: 0);
        $limit = (int)($this->getParameter('limit') ?: 50);
        $sortingRaw = $this->getParameter('sorting') ?: 'title,asc';
        $search = $this->getParameter('search') ?: null;
        $countryId = $this->getParameter('countryId') !== false ? (int)$this->getParameter('countryId') : null;
        $cityId = $this->getParameter('cityId') !== false ? (int)$this->getParameter('cityId') : null;
        $letter = $this->getParameter('letter') ?: null;
        $typesRaw = $this->getParameter('types') ?: null;
        $types = $typesRaw !== null
            ? explode(',', $typesRaw)
                |> (static fn($x) => array_map(static fn(string $t) => EntityType::tryFrom($t), $x))
                |> (static fn($x) => array_filter($x, static fn(?EntityType $t) => $t !== null && in_array($t, [EntityType::Author, EntityType::AuthorAlias], true)))
            : [EntityType::Author, EntityType::AuthorAlias];
        $items = $this->getParameter('items') ?: null;

        $sorting = SortingParams::fromRequest($sortingRaw, ['title', 'graphicsRating', 'musicRating', 'id']);
        $result = $this->authorListService->getPaged(
            $sorting,
            $start,
            $limit,
            $search,
            $countryId,
            $cityId,
            $letter,
            $types,
            $items
        );

        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                fn(AuthorListItemDto $dto) => $this->objectMapper->map($dto, AuthorListItemRestDto::class),
                $result['items']
            ),
        ]);
    }

    private function handleActive(): void
    {
        $items = $this->getParameter('items') ?: null;
        $years = (int)($this->getParameter('years') ?: AuthorListService::ACTIVE_YEARS_DEFAULT);
        $authors = $this->authorListService->getActive($items, $years);

        $this->assignSuccess([
            'items' => array_map(
                fn(ActiveAuthorDto $dto) => $this->objectMapper->map($dto, ActiveAuthorRestDto::class),
                $authors
            ),
        ]);
    }

    private function handleFilters(): void
    {
        $letter = $this->getParameter('letter') ?: null;
        $items = $this->getParameter('items') ?: null;
        $options = $this->authorListService->getFilterOptions($letter, $items);

        $this->assignSuccess(new AuthorFilterOptionsRestDto(
            countries: array_map(
                fn(FilterOptionDto $dto) => $this->objectMapper->map($dto, AuthorFilterOptionRestDto::class),
                $options['countries']
            ),
            cities: array_map(
                fn(FilterOptionDto $dto) => $this->objectMapper->map($dto, AuthorFilterOptionRestDto::class),
                $options['cities']
            ),
        ));
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

    public function getUrlName(): string
    {
        return '';
    }
}
