<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class AuthorCollaboratorsRepository extends AbstractRepository
{
    public function __construct(private Connection $db) {}

    /**
     * Returns co-authors of the given author (including aliases) with joint work counts.
     * Result sorted by total joint works desc, limited to top 30.
     *
     * @param int[] $authorIds main author + alias IDs
     * @return array<array{coAuthorId: int, pictures: int, tunes: int, prods: int, total: int}>
     */
    public function findCoAuthorStats(array $authorIds): array
    {
        if (empty($authorIds)) {
            return [];
        }

        /** @var int[] $elementIds */
        $elementIds = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('authorId', $authorIds)
            ->distinct()
            ->pluck('elementId');

        if (empty($elementIds)) {
            return [];
        }

        /** @var array<array{authorId: int, type: string}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('elementId', $elementIds)
            ->whereNotIn('authorId', $authorIds)
            ->select(['authorId', 'type'])
            ->get();

        $stats = [];
        foreach ($rows as $row) {
            $coAuthorId = $row['authorId'];
            if (!isset($stats[$coAuthorId])) {
                $stats[$coAuthorId] = [
                    'coAuthorId' => $coAuthorId,
                    'pictures'   => 0,
                    'tunes'      => 0,
                    'prods'      => 0,
                    'total'      => 0,
                ];
            }
            $stats[$coAuthorId]['total']++;
            $type = $row['type'];
            if ($type === 'picture') {
                $stats[$coAuthorId]['pictures']++;
            } elseif ($type === 'music') {
                $stats[$coAuthorId]['tunes']++;
            } elseif ($type === 'prod' || $type === 'release') {
                $stats[$coAuthorId]['prods']++;
            }
        }

        usort($stats, fn(array $a, array $b) => $b['total'] <=> $a['total']);

        return array_slice($stats, 0, 30);
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
            ->whereNotIn('authorId', fn($q) => $q->select('id')->from($aliasTable))
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
}
