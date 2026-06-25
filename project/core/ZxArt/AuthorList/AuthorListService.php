<?php

declare(strict_types=1);

namespace ZxArt\AuthorList;

use ApiQueriesManager;
use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\AuthorList\Dto\ActiveAuthorDto;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Dto\FilterOptionDto;
use ZxArt\AuthorList\Repositories\AuthorListRepository;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\SortingParams;

readonly class AuthorListService
{
    public const int ACTIVE_YEARS_DEFAULT = 2;
    public const int ACTIVE_YEARS_MIN = 1;
    public const int ACTIVE_YEARS_MAX = 5;

    public function __construct(
        private structureManager $structureManager,
        private AuthorListRepository $repository,
        private AuthorListTransformer $transformer,
        private ApiQueriesManager $apiQueriesManager,
    ) {
    }

    /**
     * Authors with works (pictures or music) published within the last $yearsBack years.
     *
     * @return ActiveAuthorDto[]
     */
    public function getActive(?string $items, int $yearsBack = self::ACTIVE_YEARS_DEFAULT): array
    {
        $yearsBack = max(self::ACTIVE_YEARS_MIN, min(self::ACTIVE_YEARS_MAX, $yearsBack));

        $currentYear = (int)date('Y');
        $years = [];
        for ($offset = 0; $offset < $yearsBack; $offset++) {
            $years[] = $currentYear - $offset;
        }

        $parameters = [];
        if ($items === 'music') {
            $parameters['zxMusicYear'] = $years;
        } else {
            $parameters['zxPictureYear'] = $years;
            $parameters['zxPictureNotType'] = 'attributes';
        }

        $query = $this->apiQueriesManager->getQuery();
        $query->setFiltrationParameters($parameters);
        $query->setExportType('author');
        $query->setOrder(['title' => 'asc']);
        $result = (array)$query->getQueryResult();

        $authors = [];
        foreach ((array)($result['author'] ?? []) as $element) {
            if ($element instanceof authorElement) {
                $authors[] = new ActiveAuthorDto(
                    id: (int)$element->id,
                    title: html_entity_decode($element->title, ENT_QUOTES),
                    url: $element->getUrl(),
                );
            }
        }

        return $authors;
    }

    /**
     * @return array{total: int, items: AuthorListItemDto[]}
     */
    public function getPaged(
        SortingParams $sorting,
        int $start,
        int $limit,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = [EntityType::Author, EntityType::AuthorAlias],
        ?string $items = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): array {
        $total = $this->repository->count($search, $countryId, $cityId, $letter, $types, $items, $north, $south, $east, $west);
        $sortColumn = AuthorSortColumn::fromString($sorting->column);
        $ids = $this->repository->findPaged($start, $limit, $sortColumn, $sorting->direction, $search, $countryId, $cityId, $letter, $types, $items, $north, $south, $east, $west);

        $dtos = [];
        foreach ($ids as $id) {
            $dto = $this->loadAndTransform($id);
            if ($dto !== null) {
                $dtos[] = $dto;
            }
        }

        return ['total' => $total, 'items' => $dtos];
    }

    /**
     * @return array{countries: FilterOptionDto[], cities: FilterOptionDto[]}
     */
    public function getFilterOptions(?string $letter = null, ?string $items = null): array
    {
        $countryIds = array_unique($this->repository->findCountryIds($letter, $items));
        $cityIds = array_unique($this->repository->findCityIds($letter, $items));

        return [
            'countries' => $this->loadLocationOptions($countryIds),
            'cities' => $this->loadLocationOptions($cityIds),
        ];
    }

    private function loadAndTransform(int $id): ?AuthorListItemDto
    {
        $element = $this->structureManager->getElementById($id)
            ?? $this->structureManager->getElementById($id, null, true);

        if ($element instanceof authorElement) {
            return $this->transformer->authorToDto($element);
        }

        if ($element instanceof authorAliasElement) {
            return $this->transformer->aliasToDto($element);
        }

        return null;
    }

    /**
     * @param int[] $locationIds
     * @return FilterOptionDto[]
     */
    private function loadLocationOptions(array $locationIds): array
    {
        $options = [];
        foreach ($locationIds as $locationId) {
            $locationElement = $this->structureManager->getElementById($locationId);
            if ($locationElement !== null) {
                $options[] = new FilterOptionDto(
                    id: (int)$locationElement->id,
                    title: html_entity_decode($locationElement->title, ENT_QUOTES),
                    url: $locationElement->getUrl(EntityType::Author->value),
                );
            }
        }
        return $options;
    }
}
