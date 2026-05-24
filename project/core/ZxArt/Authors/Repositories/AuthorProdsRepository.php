<?php

declare(strict_types=1);

namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class AuthorProdsRepository extends AbstractRepository
{
    public function __construct(private Connection $db) {}

    /**
     * Returns all prod/release IDs with metadata for the author (and aliases).
     * Releases that are children of an author's prod are excluded (no duplicates).
     *
     * @return array<array{id: int, type: string, votes: float, year: int, roles: string[]}>
     */
    public function findAllByAuthorId(int $authorId): array
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);
        $authTable = $this->tableName(DatabaseTable::Authorship);
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        /** @var list<array{id: int, votes: float, year: int, roles: string}> $prodRows */
        $prodRows = $this->db->table($prodTable)
            ->join($authTable, function ($join) use ($authTable, $prodTable, $authorIds) {
                $join->on("$authTable.elementId", '=', "$prodTable.id")
                     ->where("$authTable.type", '=', EntityType::Prod->value)
                     ->whereIn("$authTable.authorId", $authorIds);
            })
            ->select(["$prodTable.id", "$prodTable.votes", "$prodTable.year", "$authTable.roles"])
            ->get();

        $prods = $this->aggregateRows($prodRows, 'prod');

        if (empty($prods)) {
            /** @var list<array{id: int, votes: float, year: int, roles: string}> $releaseRows */
            $releaseRows = $this->db->table($releaseTable)
                ->join($authTable, function ($join) use ($authTable, $releaseTable, $authorIds) {
                    $join->on("$authTable.elementId", '=', "$releaseTable.id")
                         ->where("$authTable.type", '=', EntityType::Release->value)
                         ->whereIn("$authTable.authorId", $authorIds);
                })
                ->select(["$releaseTable.id", "$releaseTable.votes", "$releaseTable.year", "$authTable.roles"])
                ->get();

            return $this->aggregateRows($releaseRows, 'release');
        }

        $prodIds = array_column($prods, 'id');

        /** @var int[] $childReleaseIds */
        $childReleaseIds = $this->db->table($linksTable)
            ->whereIn('parentStructureId', $prodIds)
            ->where('type', '=', LinkTypes::STRUCTURE->value)
            ->pluck('childStructureId');

        /** @var list<array{id: int, votes: float, year: int, roles: string}> $releaseRows */
        $releaseRows = $this->db->table($releaseTable)
            ->join($authTable, function ($join) use ($authTable, $releaseTable, $authorIds) {
                $join->on("$authTable.elementId", '=', "$releaseTable.id")
                     ->where("$authTable.type", '=', EntityType::Release->value)
                     ->whereIn("$authTable.authorId", $authorIds);
            })
            ->whereNotIn("$releaseTable.id", $childReleaseIds)
            ->select(["$releaseTable.id", "$releaseTable.votes", "$releaseTable.year", "$authTable.roles"])
            ->get();

        return array_merge($prods, $this->aggregateRows($releaseRows, 'release'));
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
     * Deduplicates by id and merges roles from multiple authorship records.
     *
     * @param list<array{id: int, votes: float, year: int, roles: string}> $rows
     * @return array<int, array{id: int, type: string, votes: float, year: int, roles: string[]}>
     */
    private function aggregateRows(array $rows, string $type): array
    {
        $seen = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            /** @var mixed $decoded */
            $decoded = json_decode($row['roles'] ?? '[]', true);
            /** @var string[] $roles */
            $roles = is_array($decoded) ? $decoded : [];

            if (!isset($seen[$id])) {
                $seen[$id] = [
                    'id'    => $id,
                    'type'  => $type,
                    'votes' => $row['votes'],
                    'year'  => $row['year'],
                    'roles' => $roles,
                ];
            } else {
                $seen[$id]['roles'] = array_values(array_unique(array_merge($seen[$id]['roles'], $roles)));
            }
        }

        return $seen;
    }
}
