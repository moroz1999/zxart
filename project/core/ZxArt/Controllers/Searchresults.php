<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use Cache;
use controller;
use LanguagesManager;
use Monolog\Logger;
use Symfony\Component\ObjectMapper\ObjectMapper;
use structureManager;
use Throwable;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Rest\AuthorListItemRestDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\Rest\GroupListItemRestDto;
use ZxArt\Parties\Dto\PartyDto;
use ZxArt\Parties\Rest\PartyRestDto;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Rest\PictureRestDto;
use ZxArt\Press\Dto\PressArticleDto;
use ZxArt\Press\Rest\PressArticleRestDto;
use ZxArt\Prods\Dto\ProdDto;
use ZxArt\Prods\Rest\ProdRestDto;
use ZxArt\Search\Dto\SearchResultsDto;
use ZxArt\Search\Dto\SearchResultSetDto;
use ZxArt\Search\Rest\SearchResultSetRestDto;
use ZxArt\Search\Rest\SearchResultsRestDto;
use ZxArt\Search\Services\SearchService;
use ZxArt\Tunes\Dto\TuneDto;
use ZxArt\Tunes\Rest\TuneRestDto;

class Searchresults extends LoggedControllerApplication
{
    private const int QUICK_SEARCH_PAGE_SIZE = 30;

    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly SearchService $searchService,
        private readonly ObjectMapper $objectMapper,
        private readonly Cache $cache,
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
        try {
            $phrase = (string)($this->getParameter('phrase') ?? '');
            $page = $this->parsePositiveInt('page', 1);
            $types = $this->parseTypes($this->getParameter('types'));

            if ($this->getParameter('mode') === 'quick') {
                $this->cache->enable();
                $resultDto = $this->searchService->quickSearch(
                    $phrase,
                    $types,
                    self::QUICK_SEARCH_PAGE_SIZE,
                    (int)$this->languagesManager->getCurrentLanguageId(),
                );
            } else {
                $resultDto = $this->searchService->search($phrase, $page, $types);
            }
            $this->assignSuccess($this->mapResult($resultDto));
        } catch (Throwable $e) {
            $this->logThrowable('Searchresults::execute', $e);
            $this->assignError('Internal server error');
        }

        $this->renderer->display();
    }

    private function mapResult(SearchResultsDto $dto): SearchResultsRestDto
    {
        $sets = array_map(
            fn(SearchResultSetDto $set) => $this->mapSet($set),
            $dto->sets,
        );
        return new SearchResultsRestDto(
            phrase: $dto->phrase,
            page: $dto->page,
            pageSize: $dto->pageSize,
            total: $dto->total,
            sets: $sets,
        );
    }

    private function mapSet(SearchResultSetDto $set): SearchResultSetRestDto
    {
        $items = array_map(
            fn(object $item) => $this->mapItem($item),
            $set->items,
        );
        return new SearchResultSetRestDto(
            type: $set->type,
            totalCount: $set->totalCount,
            items: $items,
        );
    }

    private function mapItem(object $item): object
    {
        return match (true) {
            $item instanceof AuthorListItemDto => $this->objectMapper->map($item, AuthorListItemRestDto::class),
            $item instanceof GroupListItemDto => $this->objectMapper->map($item, GroupListItemRestDto::class),
            $item instanceof PictureDto => $this->objectMapper->map($item, PictureRestDto::class),
            $item instanceof ProdDto => $this->objectMapper->map($item, ProdRestDto::class),
            $item instanceof TuneDto => $this->objectMapper->map($item, TuneRestDto::class),
            $item instanceof PressArticleDto => $this->objectMapper->map($item, PressArticleRestDto::class),
            $item instanceof PartyDto => $this->objectMapper->map($item, PartyRestDto::class),
            default => $item,
        };
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
