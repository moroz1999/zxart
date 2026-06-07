<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Groups\GroupProdsScope;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class GroupProdsRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * @return array{items: list<array{id: int}>, total: int}
     */
    public function findPaged(
        int $groupId,
        GroupProdsScope $scope,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $type,
    ): array {
        $groupIds = $this->getGroupAndAliasIds($groupId);

        $total = $this->buildItemsQuery($groupIds, $scope, $type)
            ->count($this->tableColumn($this->itemTable($scope), 'id'));

        /** @var list<array{id: int}> $rows */
        $rows = $this->applySort($this->buildItemsQuery($groupIds, $scope, $type), $sort, $sortDir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = ['id' => (int)$row['id']];
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return string[]
     */
    public function findAvailableReleaseTypes(int $groupId): array
    {
        $groupIds = $this->getGroupAndAliasIds($groupId);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        /** @var list<string|null> $values */
        $values = $this->db->table($releaseTable)
            ->join($linksTable, "$linksTable.childStructureId", '=', "$releaseTable.id")
            ->where("$linksTable.type", '=', GroupProdsScope::Releases->linkType()->value)
            ->whereIn("$linksTable.parentStructureId", $groupIds)
            ->whereNotIn("$releaseTable.releaseType", ReleaseTypes::getGroupExcludedValues())
            ->distinct()
            ->pluck("$releaseTable.releaseType");

        $types = [];
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                $types[$value] = true;
            }
        }
        $availableTypes = array_keys($types);
        sort($availableTypes);

        return $availableTypes;
    }

    /**
     * @param int[] $groupIds
     */
    private function buildItemsQuery(array $groupIds, GroupProdsScope $scope, string $type): Builder
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
            $query->whereNotIn(
                $this->tableColumn(DatabaseTable::ZxRelease, 'releaseType'),
                ReleaseTypes::getGroupExcludedValues(),
            );
            if ($type !== '') {
                $query->where($this->tableColumn(DatabaseTable::ZxRelease, 'releaseType'), '=', $type);
            }
        }

        return $query;
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
}
