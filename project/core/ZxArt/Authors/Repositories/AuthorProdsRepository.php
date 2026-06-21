<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Authors\ProdCodingRole;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class AuthorProdsRepository extends AbstractRepository
{
    private const PUBLISHER_ROLE = 'publisher';

    public function __construct(private Connection $db) {}

    /**
     * @return array{items: list<array{id: int, roles: string[]}>, total: int}
     */
    public function findPagedByAuthorId(
        int $authorId,
        int $start,
        int $limit,
        string $sort,
        string $sortDir,
        string $role,
    ): array {
        $authorIds = $this->getAuthorAndAliasIds($authorId);

        $total = $this->countItems($authorIds, $role);
        /** @var list<array{id: int}> $itemRows */
        $itemRows = $this->getItemsQuery($authorIds, $sort, $sortDir, $role)
            ->offset($start)
            ->limit($limit)
            ->get();

        $itemIds = array_map(static fn(array $item): int => $item['id'], $itemRows);
        $rolesByItemId = $this->findRolesByItemIds($itemIds, $authorIds);

        $items = [];
        foreach ($itemRows as $itemRow) {
            $itemId = $itemRow['id'];
            $items[] = [
                'id' => $itemId,
                'roles' => $rolesByItemId[$itemId] ?? [],
            ];
        }

        return ['items' => $items, 'total' => $total];
    }

    /**
     * @return string[]
     */
    public function findAvailableRolesByAuthorId(int $authorId): array
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $roleRows = array_merge(
            $this->getProdRoleRows($authorIds),
            $this->getReleaseRoleRows($authorIds),
        );

        $roleIndex = [];
        foreach ($roleRows as $roleRow) {
            foreach ($this->decodeRoles($roleRow['roles']) as $role) {
                $roleIndex[$role] = true;
            }
        }

        if ($this->hasPublisherProds($authorIds)) {
            $roleIndex[self::PUBLISHER_ROLE] = true;
        }

        $availableRoles = array_keys($roleIndex);
        sort($availableRoles);

        return $availableRoles;
    }

    public function countByAuthorId(int $authorId): int
    {
        return $this->countItems($this->getAuthorAndAliasIds($authorId), '');
    }

    public function hasCodingRoles(int $authorId): bool
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $roles = array_map(static fn(ProdCodingRole $role): string => $role->value, ProdCodingRole::cases());

        $prodQuery = $this->applyRolesFilter($this->getProdAuthorshipQuery($authorIds), $roles);
        if ($prodQuery->exists()) {
            return true;
        }

        $releaseQuery = $this->applyRolesFilter($this->getReleaseQuery($authorIds, ''), $roles);
        return $releaseQuery->exists();
    }

    /**
     * @param int[] $authorIds
     */
    private function countItems(array $authorIds, string $role): int
    {
        $prodCount = $this->getProdQuery($authorIds, $role)
            ->distinct()
            ->count($this->tableColumn(DatabaseTable::ZxProd, 'id'));
        $releaseCount = $this->getReleaseQuery($authorIds, $role)
            ->distinct()
            ->count($this->tableColumn(DatabaseTable::ZxRelease, 'id'));

        return $prodCount + $releaseCount;
    }

    /**
     * @param int[] $authorIds
     */
    private function getItemsQuery(array $authorIds, string $sort, string $sortDir, string $role): Builder
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $structureElementsTable = $this->tableName(DatabaseTable::StructureElements);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $prefix = $this->db->getTablePrefix();
        $rawProd = $prefix . $prodTable;
        $rawRelease = $prefix . $releaseTable;
        $rawLinks = $prefix . $linksTable;
        $prodQuery = $this->getProdQuery($authorIds, $role)
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$prodTable.id")
            ->select([
                "$prodTable.id",
                "$prodTable.votes",
                "$prodTable.year",
                "$structureElementsTable.dateCreated",
                $this->db->raw("COALESCE((SELECT SUM(r.downloads) FROM $rawRelease r INNER JOIN $rawLinks sl ON sl.childStructureId = r.id AND sl.type = 'structure' AND sl.parentStructureId = $rawProd.id), 0) as downloads"),
                $this->db->raw("COALESCE((SELECT SUM(r.plays) FROM $rawRelease r INNER JOIN $rawLinks sl ON sl.childStructureId = r.id AND sl.type = 'structure' AND sl.parentStructureId = $rawProd.id), 0) as plays"),
            ])
            ->distinct();
        $releaseQuery = $this->getReleaseQuery($authorIds, $role)
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$releaseTable.id")
            ->select([
                "$releaseTable.id",
                "$releaseTable.votes",
                "$releaseTable.year",
                "$structureElementsTable.dateCreated",
                "$releaseTable.downloads",
                "$releaseTable.plays",
            ])
            ->distinct();

        $query = $prodQuery->unionAll($releaseQuery);
        $direction = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';
        if ($sort === 'year') {
            return $query
                ->orderBy('year', $direction)
                ->orderBy('dateCreated', $direction)
                ->orderBy('id', $direction);
        }
        if ($sort === 'downloads' || $sort === 'plays') {
            return $query
                ->orderBy($sort, $direction)
                ->orderBy('id', $direction);
        }

        return $query
            ->orderBy('votes', $direction)
            ->orderBy('id', $direction);
    }

    /**
     * @param int[] $authorIds
     */
    private function getProdQuery(array $authorIds, string $role): Builder
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $includeAuthorship = $role !== self::PUBLISHER_ROLE;
        $includePublisher = $role === '' || $role === self::PUBLISHER_ROLE;

        return $this->db->table($prodTable)
            ->where(function (Builder $query) use ($prodTable, $authorIds, $role, $includeAuthorship, $includePublisher): void {
                if ($includeAuthorship) {
                    $query->orWhereExists(function (Builder $sub) use ($prodTable, $authorIds, $role): void {
                        $authTable = $this->tableName(DatabaseTable::Authorship);
                        $sub->from($authTable)
                            ->whereColumn("$authTable.elementId", "$prodTable.id")
                            ->where("$authTable.type", '=', EntityType::Prod->value)
                            ->whereIn("$authTable.authorId", $authorIds);
                        $this->applyRoleFilter($sub, $role);
                    });
                }
                if ($includePublisher) {
                    $query->orWhereExists(function (Builder $sub) use ($prodTable, $authorIds): void {
                        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
                        $sub->from($linksTable)
                            ->whereColumn("$linksTable.childStructureId", "$prodTable.id")
                            ->where("$linksTable.type", '=', LinkTypes::ZX_PROD_PUBLISHERS->value)
                            ->whereIn("$linksTable.parentStructureId", $authorIds);
                    });
                }
            });
    }

    /**
     * @param int[] $authorIds
     */
    private function getProdAuthorshipQuery(array $authorIds): Builder
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $authTable = $this->tableName(DatabaseTable::Authorship);

        return $this->db->table($prodTable)
            ->join($authTable, "$authTable.elementId", '=', "$prodTable.id")
            ->where("$authTable.type", '=', EntityType::Prod->value)
            ->whereIn("$authTable.authorId", $authorIds);
    }

    /**
     * @param int[] $authorIds
     */
    private function getReleaseQuery(array $authorIds, string $role): Builder
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $authTable = $this->tableName(DatabaseTable::Authorship);
        $query = $this->db->table($releaseTable)
            ->join($authTable, "$authTable.elementId", '=', "$releaseTable.id")
            ->where("$authTable.type", '=', EntityType::Release->value)
            ->whereIn("$authTable.authorId", $authorIds);

        $this->excludeChildReleasesOfAuthorProds($query, $authorIds);

        return $this->applyRoleFilter($query, $role);
    }

    private function applyRoleFilter(Builder $query, string $role): Builder
    {
        if ($role !== '') {
            $escapedRole = addcslashes($role, '\\%_');
            $query->where(
                $this->tableColumn(DatabaseTable::Authorship, 'roles'),
                'like',
                '%"' . $escapedRole . '"%'
            );
        }

        return $query;
    }

    /**
     * @param string[] $roles
     */
    private function applyRolesFilter(Builder $query, array $roles): Builder
    {
        $column = $this->tableColumn(DatabaseTable::Authorship, 'roles');
        $query->where(static function (Builder $q) use ($column, $roles): void {
            foreach ($roles as $role) {
                $q->orWhere($column, 'like', '%"' . $role . '"%');
            }
        });

        return $query;
    }

    /**
     * @param int[] $authorIds
     */
    private function excludeChildReleasesOfAuthorProds(Builder $query, array $authorIds): void
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $authTable = $this->tableName(DatabaseTable::Authorship);

        $query->whereNotIn("$releaseTable.id", function (Builder $childReleasesQuery) use ($linksTable, $prodTable, $authTable, $authorIds): void {
            $childReleasesQuery->select("$linksTable.childStructureId")
                ->from($linksTable)
                ->where("$linksTable.type", '=', LinkTypes::STRUCTURE->value)
                ->whereIn("$linksTable.parentStructureId", function (Builder $prodsQuery) use ($prodTable, $authTable, $authorIds): void {
                    $prodsQuery->select("$prodTable.id")
                        ->from($prodTable)
                        ->join($authTable, "$authTable.elementId", '=', "$prodTable.id")
                        ->where("$authTable.type", '=', EntityType::Prod->value)
                        ->whereIn("$authTable.authorId", $authorIds)
                        ->distinct();
                });
        });
    }

    /**
     * @param int[] $itemIds
     * @param int[] $authorIds
     * @return array<int, string[]>
     */
    private function findRolesByItemIds(array $itemIds, array $authorIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        /** @var list<array{elementId: int, roles: string}> $roleRows */
        $roleRows = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->select(['elementId', 'roles'])
            ->whereIn('elementId', $itemIds)
            ->whereIn('authorId', $authorIds)
            ->whereIn('type', [EntityType::Prod->value, EntityType::Release->value])
            ->get();

        $rolesByItemId = [];
        foreach ($roleRows as $roleRow) {
            $itemId = $roleRow['elementId'];
            $roles = $this->decodeRoles($roleRow['roles']);
            $rolesByItemId[$itemId] = array_values(array_unique(array_merge($rolesByItemId[$itemId] ?? [], $roles)));
        }

        foreach ($this->findPublisherProdIds($itemIds, $authorIds) as $prodId) {
            $rolesByItemId[$prodId] = array_values(array_unique(array_merge($rolesByItemId[$prodId] ?? [], [self::PUBLISHER_ROLE])));
        }

        return $rolesByItemId;
    }

    /**
     * @param int[] $itemIds
     * @param int[] $authorIds
     * @return int[]
     */
    private function findPublisherProdIds(array $itemIds, array $authorIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        /** @var int[] $prodIds */
        $prodIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('type', '=', LinkTypes::ZX_PROD_PUBLISHERS->value)
            ->whereIn('parentStructureId', $authorIds)
            ->whereIn('childStructureId', $itemIds)
            ->distinct()
            ->pluck('childStructureId');

        return array_values(array_unique($prodIds));
    }

    /**
     * @param int[] $authorIds
     */
    private function hasPublisherProds(array $authorIds): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('type', '=', LinkTypes::ZX_PROD_PUBLISHERS->value)
            ->whereIn('parentStructureId', $authorIds)
            ->exists();
    }

    /**
     * @param int[] $authorIds
     * @return list<array{roles: string}>
     */
    private function getProdRoleRows(array $authorIds): array
    {
        $authTable = $this->tableName(DatabaseTable::Authorship);

        /** @var list<array{roles: string}> $rows */
        $rows = $this->getProdAuthorshipQuery($authorIds)
            ->select("$authTable.roles")
            ->get();

        return $rows;
    }

    /**
     * @param int[] $authorIds
     * @return list<array{roles: string}>
     */
    private function getReleaseRoleRows(array $authorIds): array
    {
        $authTable = $this->tableName(DatabaseTable::Authorship);

        /** @var list<array{roles: string}> $rows */
        $rows = $this->getReleaseQuery($authorIds, '')
            ->select("$authTable.roles")
            ->get();

        return $rows;
    }

    /**
     * @return int[]
     */
    private function getAuthorAndAliasIds(int $authorId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::AuthorAlias))
            ->where('authorId', '=', $authorId)
            ->pluck('id');

        return [$authorId, ...$aliasIds];
    }

    /**
     * @return string[]
     */
    private function decodeRoles(string $encodedRoles): array
    {
        /** @var mixed $decodedRoles */
        $decodedRoles = json_decode($encodedRoles, true);
        if (!is_array($decodedRoles)) {
            return [];
        }

        $roles = array_filter($decodedRoles, static fn(mixed $decodedRole): bool => is_string($decodedRole));

        return array_values($roles);
    }
}
