<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractRepository;

/**
 * Groups by a part of the title, of the abbreviation or of an alias, by
 * location and by type. An alias that matches answers its group, so each group
 * comes once and aliases never do.
 *
 * @psalm-api
 */
final readonly class GroupSearchRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Ordered by title.
     *
     * @return int[]
     */
    public function findIds(?string $name, ?int $countryId, ?int $cityId, ?string $type, int $start, int $limit): array
    {
        $groupTable = $this->tableName(DatabaseTable::Group);
        /** @var int[] $ids */
        $ids = $this->buildQuery($name, $countryId, $cityId, $type)
            ->select([$groupTable . '.id', $groupTable . '.title'])
            ->distinct()
            ->orderBy($groupTable . '.title')
            ->orderBy($groupTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(?string $name, ?int $countryId, ?int $cityId, ?string $type): int
    {
        return $this->buildQuery($name, $countryId, $cityId, $type)
            ->distinct()
            ->count($this->tableName(DatabaseTable::Group) . '.id');
    }

    private function buildQuery(?string $name, ?int $countryId, ?int $cityId, ?string $type): Builder
    {
        $groupTable = $this->tableName(DatabaseTable::Group);
        $aliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $query = $this->db->table($groupTable);

        if ($name !== null && $name !== '') {
            // a backslash escapes the wildcards in MariaDB
            $pattern = '%' . addcslashes($name, '\\%_') . '%';
            $query->where(static function (Builder $nameQuery) use ($groupTable, $aliasTable, $pattern): void {
                $nameQuery->where($groupTable . '.title', 'like', $pattern)
                    ->orWhere($groupTable . '.abbreviation', 'like', $pattern)
                    ->orWhereIn($groupTable . '.id', static function (Builder $aliasQuery) use ($aliasTable, $pattern): void {
                        $aliasQuery->select('groupId')->from($aliasTable)->where('title', 'like', $pattern);
                    });
            });
        }
        if ($countryId !== null) {
            $query->where($groupTable . '.country', '=', $countryId);
        }
        if ($cityId !== null) {
            $query->where($groupTable . '.city', '=', $cityId);
        }
        if ($type !== null) {
            $query->where($groupTable . '.type', '=', $type);
        }

        return $query;
    }
}
