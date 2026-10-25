<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dbal;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Authors\Dbal\AuthorshipRoleCondition;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\GroupProdRole;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\EntityType;

/**
 * Narrows the productions a catalogue query left (`$catalogueIds`, a query
 * of rows with an `id`) by the filters the catalogue does not hold: title,
 * dates, authors and groups, places, parties, series and compilations. Pages
 * them by title.
 *
 * @psalm-api
 */
final readonly class ProdSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return int[]
     */
    public function findIds(Builder $catalogueIds, ProdSearchFilterDto $filter, int $start, int $limit): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        /** @var int[] $ids */
        $ids = $this->buildQuery($catalogueIds, $filter)
            ->select([$prodTable . '.id', $prodTable . '.title'])
            ->distinct()
            ->orderBy($prodTable . '.title')
            ->orderBy($prodTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(Builder $catalogueIds, ProdSearchFilterDto $filter): int
    {
        return $this->buildQuery($catalogueIds, $filter)
            ->distinct()
            ->count($this->tableName(DatabaseTable::ZxProd) . '.id');
    }

    /**
     * The ids of the productions found, as a query of rows with an `id`, for
     * another search to narrow by.
     */
    public function idsQuery(Builder $catalogueIds, ProdSearchFilterDto $filter): Builder
    {
        return $this->buildQuery($catalogueIds, $filter)
            ->select($this->tableName(DatabaseTable::ZxProd) . '.id');
    }

    private function buildQuery(Builder $catalogueIds, ProdSearchFilterDto $filter): Builder
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $query = $this->db->table($prodTable)->whereIn($prodTable . '.id', (clone $catalogueIds)->select('id'));

        $title = $filter->title !== null ? trim($filter->title) : '';
        if ($title !== '') {
            // a backslash escapes the wildcards in MariaDB
            $pattern = '%' . addcslashes($title, '\\%_') . '%';
            $query->where(static function (Builder $titleQuery) use ($prodTable, $pattern): void {
                $titleQuery->where($prodTable . '.title', 'like', $pattern)
                    ->orWhere($prodTable . '.altTitle', 'like', $pattern);
            });
        }
        $this->applyRange($query, $prodTable . '.year', $filter->yearFrom, $filter->yearTo);
        $this->applyRange($query, $prodTable . '.dateAdded', $filter->addedFrom, $filter->addedTo);
        if ($filter->releaseYearFrom !== null || $filter->releaseYearTo !== null) {
            $query->whereIn($prodTable . '.id', $this->prodsWithReleaseYears($filter->releaseYearFrom, $filter->releaseYearTo));
        }
        $this->applyAuthors($query, $prodTable, $filter);
        if ($filter->groupIds !== []) {
            $query->whereIn($prodTable . '.id', $this->prodsOfGroups($filter->groupIds, $filter->groupRole));
        }
        if ($filter->countryIds !== []) {
            $this->applyPlace($query, $prodTable, 'country', $filter->countryIds);
        }
        if ($filter->cityIds !== []) {
            $this->applyPlace($query, $prodTable, 'city', $filter->cityIds);
        }
        if ($filter->partyIds !== []) {
            $query->whereIn($prodTable . '.party', $filter->partyIds);
        }
        if ($filter->compo !== null && $filter->compo !== '') {
            $query->where($prodTable . '.compo', '=', $filter->compo);
        }
        if ($filter->maxPartyPlace !== null) {
            $query->where($prodTable . '.partyplace', '>', 0)
                ->where($prodTable . '.partyplace', '<=', $filter->maxPartyPlace);
        }
        if ($filter->seriesId !== null) {
            $query->whereIn($prodTable . '.id', $this->linked(LinkTypes::SERIES, 'childStructureId', 'parentStructureId', $filter->seriesId));
        }
        if ($filter->compilationId !== null) {
            $query->whereIn($prodTable . '.id', $this->linked(LinkTypes::COMPILATION, 'childStructureId', 'parentStructureId', $filter->compilationId));
        }
        if ($filter->includedProdId !== null) {
            $query->whereIn($prodTable . '.id', $this->linked(LinkTypes::COMPILATION, 'parentStructureId', 'childStructureId', $filter->includedProdId));
        }

        return $query;
    }

    private function applyRange(Builder $query, string $column, ?int $from, ?int $to): void
    {
        if ($from !== null) {
            $query->where($column, '>=', $from);
        }
        if ($to !== null) {
            $query->where($column, '<=', $to);
        }
    }

    /**
     * Every author must be credited, each in one of the roles when roles are
     * given; with `anyAuthor` one of them is enough. Roles alone find what
     * anyone is credited with in them.
     */
    private function applyAuthors(Builder $query, string $prodTable, ProdSearchFilterDto $filter): void
    {
        if ($filter->authorIds === []) {
            if ($filter->authorRoles !== []) {
                $query->whereIn($prodTable . '.id', $this->credits(null, $filter->authorRoles));
            }
            return;
        }
        if ($filter->anyAuthor) {
            $query->whereIn($prodTable . '.id', $this->credits($filter->authorIds, $filter->authorRoles));
            return;
        }
        foreach ($filter->authorIds as $authorId) {
            $query->whereIn($prodTable . '.id', $this->credits([$authorId], $filter->authorRoles));
        }
    }

    /**
     * The productions credited to the authors (or their aliases), or to
     * anyone when no author is given, in one of the roles when any are given.
     *
     * @param int[]|null $authorIds
     * @param string[] $roles
     */
    private function credits(?array $authorIds, array $roles): Closure
    {
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);

        return static function (Builder $creditQuery) use ($authorshipTable, $aliasTable, $authorIds, $roles): void {
            $creditQuery->select('elementId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Prod->value);
            if ($authorIds !== null) {
                $creditQuery->where(static function (Builder $authorQuery) use ($aliasTable, $authorIds): void {
                    $authorQuery->whereIn('authorId', $authorIds)
                        ->orWhereIn('authorId', static function (Builder $aliasQuery) use ($aliasTable, $authorIds): void {
                            $aliasQuery->select('id')->from($aliasTable)->whereIn('authorId', $authorIds);
                        });
                });
            }
            AuthorshipRoleCondition::apply($creditQuery, $roles);
        };
    }

    /**
     * Made, published, or either, by the groups or their aliases.
     *
     * @param int[] $groupIds
     */
    private function prodsOfGroups(array $groupIds, ?GroupProdRole $role): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $aliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $linkTypes = match ($role) {
            GroupProdRole::Developer => [LinkTypes::ZX_PROD_GROUPS->value],
            GroupProdRole::Publisher => [LinkTypes::ZX_PROD_PUBLISHERS->value],
            null => [LinkTypes::ZX_PROD_GROUPS->value, LinkTypes::ZX_PROD_PUBLISHERS->value],
        };

        return static function (Builder $prodQuery) use ($linksTable, $aliasTable, $groupIds, $linkTypes): void {
            $prodQuery->select('childStructureId')
                ->from($linksTable)
                ->whereIn('type', $linkTypes)
                ->where(static function (Builder $groupQuery) use ($aliasTable, $groupIds): void {
                    $groupQuery->whereIn('parentStructureId', $groupIds)
                        ->orWhereIn('parentStructureId', static function (Builder $aliasQuery) use ($aliasTable, $groupIds): void {
                            $aliasQuery->select('id')->from($aliasTable)->whereIn('groupId', $groupIds);
                        });
                });
        };
    }

    /**
     * A production comes from where its credited authors and its developer
     * groups come from; an alias is where its author or group is. A publisher
     * does not place it.
     *
     * @param 'country'|'city' $column
     * @param int[] $placeIds
     */
    private function applyPlace(Builder $query, string $prodTable, string $column, array $placeIds): void
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        $authorAliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        $groupTable = $this->tableName(DatabaseTable::Group);
        $groupAliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $authorsThere = static function (Builder $authorQuery) use ($authorTable, $column, $placeIds): void {
            $authorQuery->select('id')->from($authorTable)->whereIn($column, $placeIds);
        };
        $groupsThere = static function (Builder $groupQuery) use ($groupTable, $column, $placeIds): void {
            $groupQuery->select('id')->from($groupTable)->whereIn($column, $placeIds);
        };

        $query->where(static function (Builder $placeQuery) use (
            $prodTable,
            $authorshipTable,
            $authorAliasTable,
            $linksTable,
            $groupAliasTable,
            $authorsThere,
            $groupsThere,
        ): void {
            $placeQuery->whereIn($prodTable . '.id', static function (Builder $creditQuery) use ($authorshipTable, $authorAliasTable, $authorsThere): void {
                $creditQuery->select('elementId')
                    ->from($authorshipTable)
                    ->where('type', '=', EntityType::Prod->value)
                    ->where(static function (Builder $authorQuery) use ($authorAliasTable, $authorsThere): void {
                        $authorQuery->whereIn('authorId', $authorsThere)
                            ->orWhereIn('authorId', static function (Builder $aliasQuery) use ($authorAliasTable, $authorsThere): void {
                                $aliasQuery->select('id')->from($authorAliasTable)->whereIn('authorId', $authorsThere);
                            });
                    });
            })->orWhereIn($prodTable . '.id', static function (Builder $developerQuery) use ($linksTable, $groupAliasTable, $groupsThere): void {
                $developerQuery->select('childStructureId')
                    ->from($linksTable)
                    ->where('type', '=', LinkTypes::ZX_PROD_GROUPS->value)
                    ->where(static function (Builder $groupQuery) use ($groupAliasTable, $groupsThere): void {
                        $groupQuery->whereIn('parentStructureId', $groupsThere)
                            ->orWhereIn('parentStructureId', static function (Builder $aliasQuery) use ($groupAliasTable, $groupsThere): void {
                                $aliasQuery->select('id')->from($groupAliasTable)->whereIn('groupId', $groupsThere);
                            });
                    });
            });
        });
    }

    private function prodsWithReleaseYears(?int $from, ?int $to): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);

        return function (Builder $prodQuery) use ($linksTable, $releaseTable, $from, $to): void {
            $prodQuery->select($linksTable . '.parentStructureId')
                ->from($linksTable)
                ->join($releaseTable, $releaseTable . '.id', '=', $linksTable . '.childStructureId')
                ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value);
            $this->applyRange($prodQuery, $releaseTable . '.year', $from, $to);
        };
    }

    /**
     * The `$select` side of the links of the type whose `$by` side is the id.
     */
    private function linked(LinkTypes $type, string $select, string $by, int $id): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return static function (Builder $linkQuery) use ($linksTable, $type, $select, $by, $id): void {
            $linkQuery->select($select)->from($linksTable)->where('type', '=', $type->value)->where($by, '=', $id);
        };
    }
}
