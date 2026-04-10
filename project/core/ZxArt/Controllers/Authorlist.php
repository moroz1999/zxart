<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\AuthorList\AuthorListService;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Dto\FilterOptionDto;
use ZxArt\AuthorList\Rest\AuthorFilterOptionRestDto;
use ZxArt\AuthorList\Rest\AuthorFilterOptionsRestDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\Shared\SortingParams;

class Authorlist extends LoggedControllerApplication
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
            $this->logThrowable('Authorlist::execute', $e);
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
            ? array_intersect(explode(',', $typesRaw), ['author', 'authorAlias'])
            : ['author', 'authorAlias'];

        $sorting = SortingParams::fromRequest($sortingRaw, ['title', 'graphicsRating', 'musicRating', 'id']);
        $service = $this->getService(AuthorListService::class);
        $result = $service->getPaged($sorting, $start, $limit, $search, $countryId, $cityId, $letter, $types);

        $mapper = new ObjectMapper();
        $this->assignSuccess([
            'total' => $result['total'],
            'items' => array_map(
                static fn(AuthorListItemDto $dto) => $mapper->map($dto, AuthorListItemRestDto::class),
                $result['items']
            ),
        ]);
    }

    private function handleFilters(int $elementId): void
    {
        $letter = $this->getParameter('letter') ?: null;
        $service = $this->getService(AuthorListService::class);
        $options = $service->getFilterOptions($letter);

        $mapper = new ObjectMapper();
        $this->assignSuccess(new AuthorFilterOptionsRestDto(
            countries: array_map(
                static fn(FilterOptionDto $dto) => $mapper->map($dto, AuthorFilterOptionRestDto::class),
                $options['countries']
            ),
            cities: array_map(
                static fn(FilterOptionDto $dto) => $mapper->map($dto, AuthorFilterOptionRestDto::class),
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
