<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class AuthorCollaboratorsRepository extends AbstractRepository
{
    public function __construct(private Connection $db) {}

    /**
     * Returns co-authors of the given author (including aliases) with joint work counts.
     * Result sorted by total joint works desc.
     *
     * @param int[] $authorIds main author + alias IDs
     * @return array<array{coAuthorId: int, pictures: int, tunes: int, prods: int, total: int}>
     */
    public function findCoAuthorStats(array $authorIds): array
    {
        if (empty($authorIds)) {
            return [];
        }

        $stats = [];

        /** @var int[] $elementIds */
        $elementIds = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('authorId', $authorIds)
            ->distinct()
            ->pluck('elementId');

        if (!empty($elementIds)) {
            /** @var array<array{authorId: int, type: string}> $rows */
            $rows = $this->db->table($this->tableName(DatabaseTable::Authorship))
                ->whereIn('elementId', $elementIds)
                ->whereNotIn('authorId', $authorIds)
                ->select(['authorId', 'type'])
                ->get();

            foreach ($rows as $row) {
                $type = match ($row['type']) {
                    EntityType::Prod->value, EntityType::Release->value => 'prods',
                    default => null,
                };
                if ($type !== null) {
                    $this->appendCoAuthorWork($stats, $row['authorId'], $type);
                }
            }
        }

        $this->appendLinkedCoAuthorStats($stats, $authorIds, LinkTypes::AUTHOR_PICTURE, 'pictures');
        $this->appendLinkedCoAuthorStats($stats, $authorIds, LinkTypes::AUTHOR_MUSIC, 'tunes');

        usort($stats, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);

        return $stats;
    }

    /**
     * Returns groups connected to the same prods or releases as the author.
     *
     * @param int[] $authorIds main author + alias IDs
     * @return array<array{groupId: int, years: int[], total: int}>
     */
    public function findGroupStats(array $authorIds): array
    {
        if (empty($authorIds)) {
            return [];
        }

        $authTable = $this->tableName(DatabaseTable::Authorship);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);

        /** @var int[] $prodIds */
        $prodIds = $this->db->table($authTable)
            ->whereIn('authorId', $authorIds)
            ->where('type', '=', EntityType::Prod->value)
            ->distinct()
            ->pluck('elementId');

        /** @var int[] $releaseIds */
        $releaseIds = $this->db->table($authTable)
            ->whereIn('authorId', $authorIds)
            ->where('type', '=', EntityType::Release->value)
            ->distinct()
            ->pluck('elementId');

        $stats = [];

        if (!empty($prodIds)) {
            /** @var list<array{groupId: mixed, workId: mixed, year: mixed}> $rows */
            $rows = $this->db->table($linksTable)
                ->join($prodTable, "$prodTable.id", '=', "$linksTable.childStructureId")
                ->whereIn("$linksTable.childStructureId", $prodIds)
                ->whereIn("$linksTable.type", ['zxProdGroups', 'zxProdPublishers'])
                ->select([
                    "$linksTable.parentStructureId as groupId",
                    "$linksTable.childStructureId as workId",
                    "$prodTable.year as year",
                ])
                ->get();
            $this->appendGroupRows($stats, $rows, 'prod');
        }

        if (!empty($releaseIds)) {
            /** @var list<array{groupId: mixed, workId: mixed, year: mixed}> $rows */
            $rows = $this->db->table($linksTable)
                ->join($releaseTable, "$releaseTable.id", '=', "$linksTable.childStructureId")
                ->whereIn("$linksTable.childStructureId", $releaseIds)
                ->where("$linksTable.type", '=', 'zxReleasePublishers')
                ->select([
                    "$linksTable.parentStructureId as groupId",
                    "$linksTable.childStructureId as workId",
                    "$releaseTable.year as year",
                ])
                ->get();
            $this->appendGroupRows($stats, $rows, 'release');
        }

        usort($stats, fn(array $a, array $b) => $b['total'] <=> $a['total']);

        return array_map(
            static fn(array $row): array => [
                'groupId' => $row['groupId'],
                'years' => array_values($row['years']),
                'total' => $row['total'],
            ],
            array_slice($stats, 0, 30),
        );
    }

    /**
     * Count distinct members (authors) of a group element.
     * Excludes alias IDs so each person is counted once.
     */
    public function countGroupMembers(int $groupId): int
    {
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        /** @var int */
        return $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->where('elementId', '=', $groupId)
            ->where('type', '=', 'group')
            ->whereNotIn(
                'authorId',
                fn(Builder $q): Builder => $q->select('id')->from($aliasTable),
            )
            ->distinct()
            ->count('authorId');
    }

    /**
     * @return int[]
     */
    public function getAuthorAndAliasIds(int $authorId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::AuthorAlias))
            ->where('authorId', '=', $authorId)
            ->pluck('id');
        return [$authorId, ...$aliasIds];
    }

    /**
     * @param array<int, array{groupId: int, years: int[], total: int, works: array<string, bool>}> $stats
     * @param list<array{groupId: mixed, workId: mixed, year: mixed}> $rows
     */
    private function appendGroupRows(array &$stats, array $rows, string $workType): void
    {
        foreach ($rows as $row) {
            $groupId = (int)$row['groupId'];
            $workKey = $workType . ':' . (int)$row['workId'];
            if (!isset($stats[$groupId])) {
                $stats[$groupId] = [
                    'groupId' => $groupId,
                    'years' => [],
                    'total' => 0,
                    'works' => [],
                ];
            }

            if (isset($stats[$groupId]['works'][$workKey])) {
                continue;
            }

            $stats[$groupId]['works'][$workKey] = true;
            $stats[$groupId]['total']++;

            $year = (int)$row['year'];
            if ($year > 0) {
                $stats[$groupId]['years'][$year] = $year;
            }
        }
    }

    /**
     * @param array<int, array{coAuthorId: int, pictures: int, tunes: int, prods: int, total: int}> $stats
     * @param int[] $authorIds
     * @param 'pictures'|'tunes' $statsKey
     */
    private function appendLinkedCoAuthorStats(
        array &$stats,
        array $authorIds,
        LinkTypes $linkType,
        string $statsKey,
    ): void {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        /** @var int[] $workIds */
        $workIds = $this->db->table($linksTable)
            ->whereIn('parentStructureId', $authorIds)
            ->where('type', '=', $linkType->value)
            ->distinct()
            ->pluck('childStructureId');
        if (empty($workIds)) {
            return;
        }

        /** @var list<array{authorId: int}> $rows */
        $rows = $this->db->table($linksTable)
            ->whereIn('childStructureId', $workIds)
            ->where('type', '=', $linkType->value)
            ->whereNotIn('parentStructureId', $authorIds)
            ->select(['parentStructureId as authorId', 'childStructureId'])
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $this->appendCoAuthorWork($stats, $row['authorId'], $statsKey);
        }
    }

    /**
     * @param array<int, array{coAuthorId: int, pictures: int, tunes: int, prods: int, total: int}> $stats
     * @param 'pictures'|'tunes'|'prods' $statsKey
     */
    private function appendCoAuthorWork(array &$stats, int $coAuthorId, string $statsKey): void
    {
        if (!isset($stats[$coAuthorId])) {
            $stats[$coAuthorId] = [
                'coAuthorId' => $coAuthorId,
                'pictures' => 0,
                'tunes' => 0,
                'prods' => 0,
                'total' => 0,
            ];
        }

        $stats[$coAuthorId][$statsKey]++;
        $stats[$coAuthorId]['total']++;
    }
}
