<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class GroupCollaboratorsRepository extends AbstractRepository
{
    private const int MAX_RESULTS = 30;

    public function __construct(private Connection $db)
    {
    }

    /**
     * @return int[]
     */
    public function getGroupAndAliasIds(int $groupId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::GroupAlias))
            ->where('groupId', '=', $groupId)
            ->pluck('id');

        return [$groupId, ...$aliasIds];
    }

    /**
     * Author IDs that are members of the group (authorship type=group).
     *
     * @param int[] $groupIds
     * @return int[]
     */
    public function getMemberAuthorIds(array $groupIds): array
    {
        /** @var int[] $authorIds */
        $authorIds = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('elementId', $groupIds)
            ->where('type', '=', EntityType::Group->value)
            ->distinct()
            ->pluck('authorId');

        return $authorIds;
    }

    /**
     * Number of distinct authors that are members of the given group (authorship type=group),
     * excluding author aliases so an author and their alias are not counted twice.
     */
    public function countMembers(int $groupId): int
    {
        $authorAliasTable = $this->tableName(DatabaseTable::AuthorAlias);

        /** @var int */
        return $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->where('elementId', '=', $groupId)
            ->where('type', '=', EntityType::Group->value)
            ->whereNotIn(
                'authorId',
                fn(Builder $q): Builder => $q->select('id')->from($authorAliasTable),
            )
            ->distinct()
            ->count('authorId');
    }

    /**
     * @param int[] $groupIds
     * @return int[]
     */
    public function getLinkedChildIds(array $groupIds, LinkTypes $linkType): array
    {
        /** @var int[] $childIds */
        $childIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->whereIn('parentStructureId', $groupIds)
            ->where('type', '=', $linkType->value)
            ->distinct()
            ->pluck('childStructureId');

        return $childIds;
    }

    /**
     * Co-creators credited on the given prods/releases, excluding the group's own members.
     *
     * @param int[] $prodIds
     * @param int[] $releaseIds
     * @param int[] $excludeAuthorIds
     * @return array<array{authorId: int, roles: string[], jointTotal: int}>
     */
    public function findPeopleStats(array $prodIds, array $releaseIds, array $excludeAuthorIds): array
    {
        $stats = [];
        $this->appendPeopleRows($stats, $prodIds, EntityType::Prod, $excludeAuthorIds);
        $this->appendPeopleRows($stats, $releaseIds, EntityType::Release, $excludeAuthorIds);

        $result = array_map(
            static fn(array $row): array => [
                'authorId' => $row['authorId'],
                'roles' => array_values(array_keys($row['roles'])),
                'jointTotal' => $row['jointTotal'],
            ],
            array_values($stats),
        );
        usort($result, static fn(array $a, array $b): int => $b['jointTotal'] <=> $a['jointTotal']);

        return array_slice($result, 0, self::MAX_RESULTS);
    }

    /**
     * Developer groups whose prods this group published.
     *
     * @param int[] $publishedProdIds
     * @param int[] $excludeGroupIds
     * @return array<array{groupId: int, years: int[], jointProds: int}>
     */
    public function findPublishedGroupStats(array $publishedProdIds, array $excludeGroupIds): array
    {
        if ($publishedProdIds === []) {
            return [];
        }

        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $prodTable = $this->tableName(DatabaseTable::ZxProd);

        /** @var list<array{groupId: int, workId: int, year: int|null}> $rows */
        $rows = $this->db->table($linksTable)
            ->join($prodTable, "$prodTable.id", '=', "$linksTable.childStructureId")
            ->whereIn("$linksTable.childStructureId", $publishedProdIds)
            ->where("$linksTable.type", '=', LinkTypes::ZX_PROD_GROUPS->value)
            ->whereNotIn("$linksTable.parentStructureId", $excludeGroupIds)
            ->select([
                "$linksTable.parentStructureId as groupId",
                "$linksTable.childStructureId as workId",
                "$prodTable.year as year",
            ])
            ->get();

        $stats = [];
        foreach ($rows as $row) {
            $groupId = (int)$row['groupId'];
            if (!isset($stats[$groupId])) {
                $stats[$groupId] = ['groupId' => $groupId, 'years' => [], 'jointProds' => 0];
            }
            $stats[$groupId]['jointProds']++;
            $year = (int)$row['year'];
            if ($year > 0) {
                $stats[$groupId]['years'][$year] = $year;
            }
        }

        $result = array_map(
            static fn(array $row): array => [
                'groupId' => $row['groupId'],
                'years' => array_values($row['years']),
                'jointProds' => $row['jointProds'],
            ],
            array_values($stats),
        );
        usort($result, static fn(array $a, array $b): int => $b['jointProds'] <=> $a['jointProds']);

        return array_slice($result, 0, self::MAX_RESULTS);
    }

    /**
     * @param array<int, array{authorId: int, roles: array<string, bool>, jointTotal: int}> $stats
     * @param int[] $elementIds
     * @param int[] $excludeAuthorIds
     */
    private function appendPeopleRows(array &$stats, array $elementIds, EntityType $type, array $excludeAuthorIds): void
    {
        if ($elementIds === []) {
            return;
        }

        $query = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('elementId', $elementIds)
            ->where('type', '=', $type->value)
            ->select(['authorId', 'roles']);
        if ($excludeAuthorIds !== []) {
            $query->whereNotIn('authorId', $excludeAuthorIds);
        }

        /** @var list<array{authorId: int, roles: string}> $rows */
        $rows = $query->get();
        foreach ($rows as $row) {
            $authorId = (int)$row['authorId'];
            if (!isset($stats[$authorId])) {
                $stats[$authorId] = ['authorId' => $authorId, 'roles' => [], 'jointTotal' => 0];
            }
            $stats[$authorId]['jointTotal']++;
            foreach ($this->decodeRoles($row['roles']) as $role) {
                $stats[$authorId]['roles'][$role] = true;
            }
        }
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
        $roles = array_filter($decodedRoles, static fn(mixed $role): bool => is_string($role) && $role !== 'unknown');

        return array_values($roles);
    }
}
