<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use LanguagesManager;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\Rest\GroupListItemRestDto;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Search\Dto\SearchItemDto;
use ZxArt\Search\Dto\SearchResultsDto;
use ZxArt\Search\Dto\SearchResultSetDto;
use ZxArt\Search\Rest\SearchItemRestDto;
use ZxArt\Search\Rest\SearchResultSetRestDto;
use ZxArt\Search\Rest\SearchResultsRestDto;
use ZxArt\Search\Services\SearchService;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class Searchresults extends LoggedControllerApplication
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
        try {
            $phrase = (string)($this->getParameter('phrase') ?? '');
            $page = $this->parsePositiveInt('page', 1);
            $types = $this->parseTypes($this->getParameter('types'));

            $searchService = $this->getService(SearchService::class);
            $resultDto = $searchService->search($phrase, $page, SearchService::DEFAULT_PAGE_SIZE, $types);
            $this->assignSuccess($this->mapResult($resultDto));
        } catch (Throwable $e) {
            $this->logThrowable('Searchresults::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function mapResult(SearchResultsDto $dto): SearchResultsRestDto
    {
        $mapper = new ObjectMapper();
        $sets = array_map(
            fn(SearchResultSetDto $set) => $this->mapSet($set, $mapper),
            $dto->sets,
        );
        return new SearchResultsRestDto(
            phrase: $dto->phrase,
            page: $dto->page,
            pageSize: $dto->pageSize,
            total: $dto->total,
            exactMatches: $dto->exactMatches,
            sets: $sets,
            availableTypes: $dto->availableTypes,
        );
    }

    private function mapSet(SearchResultSetDto $set, ObjectMapper $mapper): SearchResultSetRestDto
    {
        $items = array_map(
            fn(object $item) => $this->mapItem($item, $mapper),
            $set->items,
        );
        return new SearchResultSetRestDto(
            type: $set->type,
            partial: $set->partial,
            totalCount: $set->totalCount,
            items: $items,
        );
    }

    private function mapItem(object $item, ObjectMapper $mapper): object
    {
        return match (true) {
            $item instanceof AuthorListItemDto => $this->mapAuthor($item),
            $item instanceof GroupListItemDto => $this->mapGroup($item),
            $item instanceof PictureDto => $mapper->map($item, PictureRestDto::class),
            $item instanceof ProdDto => $mapper->map($item, ProdRestDto::class),
            $item instanceof TuneDto => $mapper->map($item, TuneRestDto::class),
            $item instanceof SearchItemDto => $mapper->map($item, SearchItemRestDto::class),
            default => $item,
        };
    }

    private function mapAuthor(AuthorListItemDto $dto): AuthorListItemRestDto
    {
        return new AuthorListItemRestDto(
            id: $dto->id,
            url: $dto->url,
            entityType: $dto->entityType->value,
            title: $dto->title,
            realName: $dto->realName,
            realNameUrl: $dto->realNameUrl,
            groups: $dto->groups,
            countryId: $dto->countryId,
            countryTitle: $dto->countryTitle,
            countryUrl: $dto->countryUrl,
            cityId: $dto->cityId,
            cityTitle: $dto->cityTitle,
            cityUrl: $dto->cityUrl,
            musicRating: $dto->musicRating,
            graphicsRating: $dto->graphicsRating,
        );
    }

    private function mapGroup(GroupListItemDto $dto): GroupListItemRestDto
    {
        return new GroupListItemRestDto(
            id: $dto->id,
            url: $dto->url,
            entityType: $dto->entityType->value,
            title: $dto->title,
            groupType: $dto->groupType,
            realGroupTitle: $dto->realGroupTitle,
            realGroupUrl: $dto->realGroupUrl,
            countryId: $dto->countryId,
            countryTitle: $dto->countryTitle,
            countryUrl: $dto->countryUrl,
            cityId: $dto->cityId,
            cityTitle: $dto->cityTitle,
            cityUrl: $dto->cityUrl,
        );
    }

    /**
     * @return string[]
     */
    private function parseTypes(mixed $raw): array
    {
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $parts = array_map('trim', explode(',', $raw));
        return array_values(array_filter($parts, static fn(string $part) => $part !== ''));
    }

    private function parsePositiveInt(string $name, int $default): int
    {
        $value = $this->getParameter($name);
        if ($value === null || $value === '' || $value === false) {
            return $default;
        }
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($intValue === false || $intValue < 1) {
            return $default;
        }
        return $intValue;
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
