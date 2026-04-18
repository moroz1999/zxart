<?php

declare(strict_types=1);

namespace ZxArt\AuthorList;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\AuthorList\Dto\FilterOptionDto;
use ZxArt\AuthorList\Repositories\AuthorListRepository;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\SortingParams;

readonly class AuthorListService
{
    public function __construct(
        private structureManager $structureManager,
        private AuthorListRepository $repository,
        private AuthorListTransformer $transformer,
    ) {
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
    ): array {
        $total = $this->repository->count($search, $countryId, $cityId, $letter, $types, $items);
        $sortColumn = AuthorSortColumn::fromString($sorting->column);
        $ids = $this->repository->findPaged($start, $limit, $sortColumn, $sorting->direction, $search, $countryId, $cityId, $letter, $types, $items);

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
