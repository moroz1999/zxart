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
use ZxArt\GroupList\Dto\FilterOptionDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\GroupListService;
use ZxArt\GroupList\Rest\GroupFilterOptionRestDto;
use ZxArt\GroupList\Rest\GroupFilterOptionsRestDto;
use ZxArt\GroupList\Rest\GroupListItemRestDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\SortingParams;

class Grouplist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly GroupListService $groupListService,
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
                $this->handleFilters($elementId);
            } else {
                $this->handleList($elementId);
            }
        } catch (Throwable $e) {
            $this->logThrowable('Grouplist::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function handleList(int $elementId): void
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
            ? array_filter(
                array_map(
                    static fn(string $t) => EntityType::tryFrom($t),
                    explode(',', $typesRaw),
                ),
                static fn(?EntityType $t) => $t !== null && in_array($t, [EntityType::Group, EntityType::GroupAlias], true),
            )
            : [EntityType::Group, EntityType::GroupAlias];
        $groupType = $this->getParameter('groupType') ?: null;

        $sorting = SortingParams::fromRequest($sortingRaw, ['title', 'id']);
        $result = $this->groupListService->getPaged(
            $sorting,
            $start,
            $limit,
            $search,
            $countryId,
            $cityId,
            $letter,
            $types,
            $groupType
        );

        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                fn(GroupListItemDto $dto) => $this->objectMapper->map($dto, GroupListItemRestDto::class),
                $result['items']
            ),
        ]);
    }

    private function handleFilters(int $elementId): void
    {
        $letter = $this->getParameter('letter') ?: null;
        $groupType = $this->getParameter('groupType') ?: null;
        $options = $this->groupListService->getFilterOptions($letter, $groupType);

        $this->assignSuccess(new GroupFilterOptionsRestDto(
            countries: array_map(
                fn(FilterOptionDto $dto) => $this->objectMapper->map($dto, GroupFilterOptionRestDto::class),
                $options['countries']
            ),
            cities: array_map(
                fn(FilterOptionDto $dto) => $this->objectMapper->map($dto, GroupFilterOptionRestDto::class),
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
