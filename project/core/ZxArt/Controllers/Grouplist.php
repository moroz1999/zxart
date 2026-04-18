<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\GroupList\Dto\FilterOptionDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\GroupListService;
use ZxArt\GroupList\Rest\GroupFilterOptionRestDto;
use ZxArt\GroupList\Rest\GroupFilterOptionsRestDto;
use ZxArt\GroupList\Rest\GroupListItemRestDto;
use ZxArt\Shared\SortingParams;

class Grouplist extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();

        $structureManager = $this->getService('publicStructureManager');
        $languagesManager = $this->getService(LanguagesManager::class);
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
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
            ? array_intersect(explode(',', $typesRaw), ['group', 'groupAlias'])
            : ['group', 'groupAlias'];
        $groupType = $this->getParameter('groupType') ?: null;

        $sorting = SortingParams::fromRequest($sortingRaw, ['title', 'id']);
        $service = $this->getService(GroupListService::class);
        $result = $service->getPaged($sorting, $start, $limit, $search, $countryId, $cityId, $letter, $types, $groupType);

        $mapper = new ObjectMapper();
        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                static fn(GroupListItemDto $dto) => $mapper->map($dto, GroupListItemRestDto::class),
                $result['items']
            ),
        ]);
    }

    private function handleFilters(int $elementId): void
    {
        $letter = $this->getParameter('letter') ?: null;
        $groupType = $this->getParameter('groupType') ?: null;
        $service = $this->getService(GroupListService::class);
        $options = $service->getFilterOptions($letter, $groupType);

        $mapper = new ObjectMapper();
        $this->assignSuccess(new GroupFilterOptionsRestDto(
            countries: array_map(
                static fn(FilterOptionDto $dto) => $mapper->map($dto, GroupFilterOptionRestDto::class),
                $options['countries']
            ),
            cities: array_map(
                static fn(FilterOptionDto $dto) => $mapper->map($dto, GroupFilterOptionRestDto::class),
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
