<?php

declare(strict_types=1);

namespace ZxArt\GroupList;

use groupAliasElement;
use groupElement;
use structureManager;
use ZxArt\GroupList\Dto\FilterOptionDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\Repositories\GroupListRepository;
use ZxArt\Shared\SortingParams;

readonly class GroupListService
{
    public function __construct(
        private structureManager $structureManager,
        private GroupListRepository $repository,
        private GroupListTransformer $transformer,
    ) {
    }

    /**
     * @return array{total: int, items: GroupListItemDto[]}
     */
    public function getPaged(
        SortingParams $sorting,
        int $start,
        int $limit,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = ['group', 'groupAlias'],
        ?string $groupType = null,
    ): array {
        $total = $this->repository->count($search, $countryId, $cityId, $letter, $types, $groupType);
        $sortColumn = GroupSortColumn::fromString($sorting->column);
        $ids = $this->repository->findPaged($start, $limit, $sortColumn, $sorting->direction, $search, $countryId, $cityId, $letter, $types, $groupType);

        $items = [];
        foreach ($ids as $id) {
            $dto = $this->loadAndTransform($id);
            if ($dto !== null) {
                $items[] = $dto;
            }
        }

        return ['total' => $total, 'items' => $items];
    }

    /**
     * @return array{countries: FilterOptionDto[], cities: FilterOptionDto[]}
     */
    public function getFilterOptions(?string $letter = null, ?string $groupType = null): array
    {
        $countryIds = array_unique($this->repository->findCountryIds($letter, $groupType));
        $cityIds = array_unique($this->repository->findCityIds($letter, $groupType));

        return [
            'countries' => $this->loadLocationOptions($countryIds),
            'cities' => $this->loadLocationOptions($cityIds),
        ];
    }

    private function loadAndTransform(int $id): ?GroupListItemDto
    {
        $element = $this->structureManager->getElementById($id)
            ?? $this->structureManager->getElementById($id, null, true);

        if ($element instanceof groupElement) {
            return $this->transformer->groupToDto($element);
        }

        if ($element instanceof groupAliasElement) {
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
                    url: $locationElement->getUrl('group'),
                );
            }
        }
        return $options;
    }
}
