<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Groups\GroupProdsScope;
use ZxArt\LinkTypes;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class GroupProdsRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * @return array{items: list<array{id: int, type: 'prod'|'release'}>, total: int}
     */
    public function findPaged(
        int $groupId,
        GroupProdsScope $scope,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $type,
        int $categoryId,
    ): array {
        $groupIds = $this->getGroupAndAliasIds($groupId);
        if ($scope === GroupProdsScope::Published) {
            return $this->findPublishedPaged($groupIds, $start, $limit, $sort, $sortDir, $categoryId);
        }

        $total = $this->countByGroupIds($groupIds, $scope, $type, $categoryId);

        /** @var list<array{id: int|string}> $rows */
        $rows = $this->applySort($this->buildItemsQuery($groupIds, $scope, $type, $categoryId), $sort, $sortDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = ['id' => (int)$row['id'], 'type' => $scope->isReleases() ? 'release' : 'prod'];
        }

        return ['items' => $items, 'total' => $total];
    }

    public function count(int $groupId, GroupProdsScope $scope, string $type = '', int $categoryId = 0): int
    {
        return $this->countByGroupIds($this->getGroupAndAliasIds($groupId), $scope, $type, $categoryId);
    }

    /**
     * @return string[]
     */
    public function findAvailableReleaseTypes(int $groupId): array
    {
        $groupIds = $this->getGroupAndAliasIds($groupId);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        $values = $this->db->table($releaseTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$releaseTable.id")
            ->where("$linksTable.type", '=', GroupProdsScope::Releases->linkType()->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->distinct();
        $this->applyReleaseFilters($values, $releaseTable, $linksTable, $groupIds);

        /** @var list<string|null> $typeValues */
        $typeValues = $values->pluck("$releaseTable.releaseType");

        $types = [];
        foreach ($typeValues as $value) {
            if ($value !== null && $value !== '') {
                $types[$value] = true;
            }
        }
        $availableTypes = array_keys($types);
        sort($availableTypes);

        return $availableTypes;
    }

    /**
     * @return int[]
     */
    public function findAvailableCategoryIds(int $groupId, GroupProdsScope $scope): array
    {
        $groupIds = $this->getGroupAndAliasIds($groupId);
        $categoryIds = match ($scope) {
            GroupProdsScope::Own => $this->findOwnCategoryIds($groupIds),
            GroupProdsScope::Published => $this->findPublishedCategoryIds($groupIds),
            GroupProdsScope::Releases => [],
        };

        sort($categoryIds);

        return $categoryIds;
    }

    /**
     * @param int[] $groupIds
     */
    private function buildItemsQuery(array $groupIds, GroupProdsScope $scope, string $type, int $categoryId): Builder
    {
        $itemTable = $this->tableName($this->itemTable($scope));
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $structureElementsTable = $this->tableName(DatabaseTable::StructureElements);

        $query = $this->db->table($itemTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$itemTable.id")
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$itemTable.id")
            ->where("$linksTable.type", '=', $scope->linkType()->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->select([
                "$itemTable.id",
                "$itemTable.votes",
                "$itemTable.year",
                "$structureElementsTable.dateCreated",
            ])
            ->distinct();

        if ($scope->isReleases()) {
            $this->applyReleaseFilters($query, $itemTable, $linksTable, $groupIds, $type);
        } elseif ($categoryId > 0) {
            $this->applyProdCategoryFilter($query, "$itemTable.id", $categoryId);
        }

        return $query;
    }

    /**
     * @param int[] $groupIds
     */
    private function countByGroupIds(array $groupIds, GroupProdsScope $scope, string $type, int $categoryId): int
    {
        if ($scope === GroupProdsScope::Published) {
            return count($this->getPublishedRows($groupIds, $categoryId));
        }

        /** @var int */
        return $this->buildItemsQuery($groupIds, $scope, $type, $categoryId)
            ->count($this->tableColumn($this->itemTable($scope), 'id'));
    }

    /**
     * @param int[] $groupIds
     * @return array{items: list<array{id: int, type: 'prod'|'release'}>, total: int}
     */
    private function findPublishedPaged(
        array $groupIds,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        int $categoryId,
    ): array {
        $rows = $this->getPublishedRows($groupIds, $categoryId);
        $this->sortRows($rows, $sort, $sortDir);

        $items = array_map(
            static fn(array $row): array => ['id' => $row['id'], 'type' => $row['type']],
            array_slice($rows, $start, $limit),
        );

        return ['items' => $items, 'total' => count($rows)];
    }

    /**
     * @param int[] $groupIds
     * @return list<array{id: int, type: 'prod'|'release', votes: float, year: int, dateCreated: int}>
     */
    private function getPublishedRows(array $groupIds, int $categoryId): array
    {
        return [
            ...$this->getPublishedProdRows($groupIds, $categoryId),
            ...$this->getPublishedReleaseRows($groupIds, $categoryId),
        ];
    }

    /**
     * @param int[] $groupIds
     * @return list<array{id: int, type: 'prod', votes: float, year: int, dateCreated: int}>
     */
    private function getPublishedProdRows(array $groupIds, int $categoryId): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $structureElementsTable = $this->tableName(DatabaseTable::StructureElements);

        /** @var list<array{id: int|string, votes: float|string|null, year: int|string|null, dateCreated: int|string|null}> $rows */
        $query = $this->db->table($prodTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$prodTable.id")
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$prodTable.id")
            ->where("$linksTable.type", '=', LinkTypes::ZX_PROD_PUBLISHERS->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->select([
                "$prodTable.id",
                "$prodTable.votes",
                "$prodTable.year",
                "$structureElementsTable.dateCreated",
            ])
            ->distinct();
        if ($categoryId > 0) {
            $this->applyProdCategoryFilter($query, "$prodTable.id", $categoryId);
        }

        /** @var list<array{id: int|string, votes: float|string|null, year: int|string|null, dateCreated: int|string|null}> $rows */
        $rows = $query->get();

        return array_map(
            static fn(array $row): array => [
                'id' => (int)$row['id'],
                'type' => 'prod',
                'votes' => (float)$row['votes'],
                'year' => (int)$row['year'],
                'dateCreated' => (int)$row['dateCreated'],
            ],
            $rows,
        );
    }

    /**
     * @param int[] $groupIds
     * @return list<array{id: int, type: 'release', votes: float, year: int, dateCreated: int}>
     */
    private function getPublishedReleaseRows(array $groupIds, int $categoryId): array
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $structureElementsTable = $this->tableName(DatabaseTable::StructureElements);

        /** @var list<array{id: int|string, votes: float|string|null, year: int|string|null, dateCreated: int|string|null}> $rows */
        $query = $this->db->table($releaseTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$releaseTable.id")
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$releaseTable.id")
            ->where("$linksTable.type", '=', LinkTypes::ZX_RELEASE_PUBLISHERS->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->whereIn("$releaseTable.releaseType", ReleaseTypes::getGroupPublishedValues())
            ->select([
                "$releaseTable.id",
                "$releaseTable.votes",
                "$releaseTable.year",
                "$structureElementsTable.dateCreated",
            ])
            ->distinct();
        if ($categoryId > 0) {
            $this->applyReleaseParentCategoryFilter($query, "$releaseTable.id", $categoryId);
        }

        /** @var list<array{id: int|string, votes: float|string|null, year: int|string|null, dateCreated: int|string|null}> $rows */
        $rows = $query->get();

        return array_map(
            static fn(array $row): array => [
                'id' => (int)$row['id'],
                'type' => 'release',
                'votes' => (float)$row['votes'],
                'year' => (int)$row['year'],
                'dateCreated' => (int)$row['dateCreated'],
            ],
            $rows,
        );
    }

    /**
     * @param int[] $groupIds
     * @return int[]
     */
    private function findOwnCategoryIds(array $groupIds): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $categoryLinksAlias = 'category_links';

        /** @var list<int|string> $ids */
        $ids = $this->db->table($prodTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$prodTable.id")
            ->join(
                "$linksTable as $categoryLinksAlias",
                "$categoryLinksAlias.childStructureId",
                '=',
                "$prodTable.id",
            )
            ->where("$linksTable.type", '=', LinkTypes::ZX_PROD_GROUPS->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->where("$categoryLinksAlias.type", '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->distinct()
            ->pluck("$categoryLinksAlias.parentStructureId");

        return $this->uniqueIntIds($ids);
    }

    /**
     * @param int[] $groupIds
     * @return int[]
     */
    private function findPublishedCategoryIds(array $groupIds): array
    {
        return $this->uniqueIntIds([
            ...$this->findPublishedProdCategoryIds($groupIds),
            ...$this->findPublishedReleaseCategoryIds($groupIds),
        ]);
    }

    /**
     * @param int[] $groupIds
     * @return int[]
     */
    private function findPublishedProdCategoryIds(array $groupIds): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $categoryLinksAlias = 'category_links';

        /** @var list<int|string> $ids */
        $ids = $this->db->table($prodTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$prodTable.id")
            ->join(
                "$linksTable as $categoryLinksAlias",
                "$categoryLinksAlias.childStructureId",
                '=',
                "$prodTable.id",
            )
            ->where("$linksTable.type", '=', LinkTypes::ZX_PROD_PUBLISHERS->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->where("$categoryLinksAlias.type", '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->distinct()
            ->pluck("$categoryLinksAlias.parentStructureId");

        return $this->uniqueIntIds($ids);
    }

    /**
     * @param int[] $groupIds
     * @return int[]
     */
    private function findPublishedReleaseCategoryIds(array $groupIds): array
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $releaseParentLinksAlias = 'release_parent_links';
        $categoryLinksAlias = 'category_links';

        /** @var list<int|string> $ids */
        $ids = $this->db->table($releaseTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$releaseTable.id")
            ->join(
                "$linksTable as $releaseParentLinksAlias",
                "$releaseParentLinksAlias.childStructureId",
                '=',
                "$releaseTable.id",
            )
            ->join(
                "$linksTable as $categoryLinksAlias",
                "$categoryLinksAlias.childStructureId",
                '=',
                "$releaseParentLinksAlias.parentStructureId",
            )
            ->where("$linksTable.type", '=', LinkTypes::ZX_RELEASE_PUBLISHERS->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->whereIn("$releaseTable.releaseType", ReleaseTypes::getGroupPublishedValues())
            ->where("$releaseParentLinksAlias.type", '=', LinkTypes::STRUCTURE->value)
            ->where("$categoryLinksAlias.type", '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->distinct()
            ->pluck("$categoryLinksAlias.parentStructureId");

        return $this->uniqueIntIds($ids);
    }

    private function applyProdCategoryFilter(Builder $query, string $prodIdColumn, int $categoryId): void
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $query->whereIn(
            $prodIdColumn,
            static function (Builder $categoryQuery) use ($linksTable, $categoryId): void {
                $categoryQuery
                    ->select('childStructureId')
                    ->from($linksTable)
                    ->where('type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
                    ->where('parentStructureId', '=', $categoryId);
            },
        );
    }

    private function applyReleaseParentCategoryFilter(Builder $query, string $releaseIdColumn, int $categoryId): void
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $releaseParentLinksAlias = 'release_parent_links';
        $categoryLinksAlias = 'category_links';

        $query->whereIn(
            $releaseIdColumn,
            static function (Builder $categoryQuery) use (
                $linksTable,
                $releaseParentLinksAlias,
                $categoryLinksAlias,
                $categoryId,
            ): void {
                $categoryQuery
                    ->select("$releaseParentLinksAlias.childStructureId")
                    ->from("$linksTable as $releaseParentLinksAlias")
                    ->join(
                        "$linksTable as $categoryLinksAlias",
                        "$categoryLinksAlias.childStructureId",
                        '=',
                        "$releaseParentLinksAlias.parentStructureId",
                    )
                    ->where("$releaseParentLinksAlias.type", '=', LinkTypes::STRUCTURE->value)
                    ->where("$categoryLinksAlias.type", '=', LinkTypes::ZX_PROD_CATEGORY->value)
                    ->where("$categoryLinksAlias.parentStructureId", '=', $categoryId);
            },
        );
    }

    /**
     * @param int[] $groupIds
     */
    private function applyReleaseFilters(
        Builder $query,
        string $releaseTable,
        string $linksTable,
        array $groupIds,
        string $type = '',
    ): void {
        $query->whereIn("$releaseTable.releaseType", ReleaseTypes::getGroupHackerValues());
        if ($type !== '') {
            $query->where("$releaseTable.releaseType", '=', $type);
        }

        $this->excludeReleasesFromGroupProds($query, $releaseTable, $linksTable, $groupIds);
    }

    /**
     * @param int[] $groupIds
     */
    private function excludeReleasesFromGroupProds(
        Builder $query,
        string $releaseTable,
        string $linksTable,
        array $groupIds,
    ): void {
        $releaseParentLinksAlias = 'release_parent_links';
        $groupProdLinksAlias = 'group_prod_links';
        $groupProdLinkTypes = [
            LinkTypes::ZX_PROD_GROUPS->value,
            LinkTypes::ZX_PROD_PUBLISHERS->value,
        ];

        $query->whereNotIn(
            "$releaseTable.id",
            function (Builder $excludedReleases) use (
                $linksTable,
                $groupIds,
                $releaseParentLinksAlias,
                $groupProdLinksAlias,
                $groupProdLinkTypes,
            ): void {
                $excludedReleases
                    ->select("$releaseParentLinksAlias.childStructureId")
                    ->from("$linksTable as $releaseParentLinksAlias")
                    ->join(
                        "$linksTable as $groupProdLinksAlias",
                        "$groupProdLinksAlias.childStructureId",
                        '=',
                        "$releaseParentLinksAlias.parentStructureId",
                    )
                    ->where("$releaseParentLinksAlias.type", '=', LinkTypes::STRUCTURE->value)
                    ->whereIn("$groupProdLinksAlias.parentStructureId", $groupIds)
                    ->whereIn("$groupProdLinksAlias.type", $groupProdLinkTypes);
            },
        );
    }

    private function applySort(Builder $query, string $sort, string $sortDir): Builder
    {
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';
        if ($sort === 'year') {
            return $query
                ->orderBy('year', $direction)
                ->orderBy('dateCreated', $direction)
                ->orderBy('id', $direction);
        }

        return $query
            ->orderBy('votes', $direction)
            ->orderBy('id', $direction);
    }

    /**
     * @param list<array{id: int, type: 'prod'|'release', votes: float, year: int, dateCreated: int}> $rows
     */
    private function sortRows(array &$rows, string $sort, string $sortDir): void
    {
        $direction = strtolower($sortDir) === 'asc' ? 1 : -1;
        usort(
            $rows,
            static function (array $a, array $b) use ($sort, $direction): int {
                if ($sort === 'year') {
                    return $direction * (
                        ($a['year'] <=> $b['year'])
                        ?: ($a['dateCreated'] <=> $b['dateCreated'])
                        ?: ($a['id'] <=> $b['id'])
                    );
                }

                return $direction * (
                    ($a['votes'] <=> $b['votes'])
                    ?: ($a['id'] <=> $b['id'])
                );
            },
        );
    }

    private function itemTable(GroupProdsScope $scope): DatabaseTable
    {
        return $scope->isReleases() ? DatabaseTable::ZxRelease : DatabaseTable::ZxProd;
    }

    /**
     * @return int[]
     */
    private function getGroupAndAliasIds(int $groupId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::GroupAlias))
            ->where('groupId', '=', $groupId)
            ->pluck('id');

        return [$groupId, ...$aliasIds];
    }

    /**
     * @param array<int|string> $ids
     * @return int[]
     */
    private function uniqueIntIds(array $ids): array
    {
        $uniqueIds = [];
        foreach ($ids as $id) {
            $intId = (int)$id;
            $uniqueIds[$intId] = $intId;
        }

        return array_values($uniqueIds);
    }
}
