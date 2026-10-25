<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dbal;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Groups\Dto\GroupSearchFilterDto;
use ZxArt\LinkTypes;
use ZxArt\Prods\ProdLink;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\EntityType;

/**
 * Groups by a part of the title, of the abbreviation or of an alias, by
 * location, type and dates, by members, by the productions they made or
 * published and by subgroup links. What an alias matches, made, published or
 * holds answers its group, so each group comes once and aliases never do.
 *
 * @psalm-api
 */
final readonly class GroupSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Ordered by title.
     *
     * @param Builder|null $foundProdIds the productions a nested search found, rows with an `id`
     * @return int[]
     */
    public function findIds(GroupSearchFilterDto $filter, ?Builder $foundProdIds, int $start, int $limit): array
    {
        $groupTable = $this->tableName(DatabaseTable::Group);
        /** @var int[] $ids */
        $ids = $this->buildQuery($filter, $foundProdIds)
            ->select([$groupTable . '.id', $groupTable . '.title'])
            ->distinct()
            ->orderBy($groupTable . '.title')
            ->orderBy($groupTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(GroupSearchFilterDto $filter, ?Builder $foundProdIds): int
    {
        return $this->buildQuery($filter, $foundProdIds)
            ->distinct()
            ->count($this->tableName(DatabaseTable::Group) . '.id');
    }

    private function buildQuery(GroupSearchFilterDto $filter, ?Builder $foundProdIds): Builder
    {
        $groupTable = $this->tableName(DatabaseTable::Group);
        $aliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $query = $this->db->table($groupTable);

        $name = $filter->name !== null ? trim($filter->name) : '';
        if ($name !== '') {
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
        if ($filter->countryIds !== []) {
            $query->whereIn($groupTable . '.country', $filter->countryIds);
        }
        if ($filter->cityIds !== []) {
            $query->whereIn($groupTable . '.city', $filter->cityIds);
        }
        if ($filter->types !== []) {
            $query->whereIn($groupTable . '.type', $filter->types);
        }
        $this->applyDateRange($query, $groupTable . '.startDate', $filter->foundedFrom, $filter->foundedTo);
        $this->applyDateRange($query, $groupTable . '.endDate', $filter->endedFrom, $filter->endedTo);
        if ($filter->memberIds !== []) {
            $this->whereRecorded($query, $this->groupsOfMembers($filter->memberIds));
        }
        if ($filter->activeFrom !== null || $filter->activeTo !== null) {
            $this->whereRecorded($query, $this->groupsOfProds($this->prodsOfYears($filter->activeFrom, $filter->activeTo), null));
        }
        if ($foundProdIds !== null) {
            $this->whereRecorded($query, $this->groupsOfProds((clone $foundProdIds)->select('id'), $filter->prodLink));
        }
        if ($filter->prodIds !== []) {
            $this->whereRecorded($query, $this->groupsOfProds($filter->prodIds, $filter->prodLink));
        } elseif ($foundProdIds === null && $filter->prodLink !== null) {
            $this->whereRecorded($query, $this->groupsOfProds(null, $filter->prodLink));
        }
        if ($filter->parentGroupId !== null) {
            $query->whereIn($groupTable . '.id', $this->subgroupLinks('childStructureId', 'parentStructureId', $filter->parentGroupId));
        }
        if ($filter->subGroupId !== null) {
            $query->whereIn($groupTable . '.id', $this->subgroupLinks('parentStructureId', 'childStructureId', $filter->subGroupId));
        }

        return $query;
    }

    private function applyDateRange(Builder $query, string $column, ?int $from, ?int $to): void
    {
        if ($from === null && $to === null) {
            return;
        }
        $query->where($column, '>', 0);
        if ($from !== null) {
            $query->where($column, '>=', $from);
        }
        if ($to !== null) {
            $query->where($column, '<=', $to);
        }
    }

    /**
     * The group is the one recorded, or the group of the alias recorded.
     *
     * @param Closure $recordedIds a query of group or group alias ids
     */
    private function whereRecorded(Builder $query, Closure $recordedIds): void
    {
        $groupTable = $this->tableName(DatabaseTable::Group);
        $aliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $query->where(static function (Builder $recordedQuery) use ($groupTable, $aliasTable, $recordedIds): void {
            $recordedQuery->whereIn($groupTable . '.id', $recordedIds)
                ->orWhereIn($groupTable . '.id', static function (Builder $aliasQuery) use ($aliasTable, $recordedIds): void {
                    $aliasQuery->select('groupId')->from($aliasTable)->whereIn('id', $recordedIds);
                });
        });
    }

    /**
     * The groups the authors or their aliases are recorded as members of.
     *
     * @param int[] $authorIds
     */
    private function groupsOfMembers(array $authorIds): Closure
    {
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $authorAliasTable = $this->tableName(DatabaseTable::AuthorAlias);

        return static function (Builder $memberQuery) use ($authorshipTable, $authorAliasTable, $authorIds): void {
            $memberQuery->select('elementId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Group->value)
                ->where(static function (Builder $authorQuery) use ($authorAliasTable, $authorIds): void {
                    $authorQuery->whereIn('authorId', $authorIds)
                        ->orWhereIn('authorId', static function (Builder $aliasQuery) use ($authorAliasTable, $authorIds): void {
                            $aliasQuery->select('id')->from($authorAliasTable)->whereIn('authorId', $authorIds);
                        });
                });
        };
    }

    /**
     * The groups that made, published, or either, the productions (any when
     * `null`).
     *
     * @param int[]|Builder|Closure|null $prodIds
     */
    private function groupsOfProds(array|Builder|Closure|null $prodIds, ?ProdLink $role): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $linkTypes = match ($role) {
            ProdLink::Made => [LinkTypes::ZX_PROD_GROUPS->value],
            ProdLink::Published => [LinkTypes::ZX_PROD_PUBLISHERS->value],
            null => [LinkTypes::ZX_PROD_GROUPS->value, LinkTypes::ZX_PROD_PUBLISHERS->value],
        };

        return static function (Builder $linkQuery) use ($linksTable, $prodIds, $linkTypes): void {
            $linkQuery->select('parentStructureId')->from($linksTable)->whereIn('type', $linkTypes);
            if ($prodIds !== null) {
                $linkQuery->whereIn('childStructureId', $prodIds);
            }
        };
    }

    /**
     * Productions with a known year in the range.
     */
    private function prodsOfYears(?int $from, ?int $to): Closure
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);

        return static function (Builder $prodQuery) use ($prodTable, $from, $to): void {
            $prodQuery->select('id')->from($prodTable)->where('year', '>', 0);
            if ($from !== null) {
                $prodQuery->where('year', '>=', $from);
            }
            if ($to !== null) {
                $prodQuery->where('year', '<=', $to);
            }
        };
    }

    private function subgroupLinks(string $select, string $by, int $groupId): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return static function (Builder $linkQuery) use ($linksTable, $select, $by, $groupId): void {
            $linkQuery->select($select)
                ->from($linksTable)
                ->where('type', '=', LinkTypes::GROUP_SUBGROUP->value)
                ->where($by, '=', $groupId);
        };
    }
}
