<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dbal;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Authors\Dto\AuthorSearchFilterDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\EntityType;

/**
 * Authors by a part of the nickname, of the real name in any language or of an
 * alias, by location, by group membership and by the productions they are
 * credited on. What an alias matches, is credited with or is a member of
 * answers its author, so each author comes once and aliases never do.
 *
 * @psalm-api
 */
final readonly class AuthorSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Ordered by nickname.
     *
     * @param Builder|null $foundProdIds the productions a nested search found, rows with an `id`
     * @return int[]
     */
    public function findIds(AuthorSearchFilterDto $filter, ?Builder $foundProdIds, int $start, int $limit): array
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        /** @var int[] $ids */
        $ids = $this->buildQuery($filter, $foundProdIds)
            ->select([$authorTable . '.id', $authorTable . '.title'])
            ->distinct()
            ->orderBy($authorTable . '.title')
            ->orderBy($authorTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(AuthorSearchFilterDto $filter, ?Builder $foundProdIds): int
    {
        return $this->buildQuery($filter, $foundProdIds)
            ->distinct()
            ->count($this->tableName(DatabaseTable::Author) . '.id');
    }

    private function buildQuery(AuthorSearchFilterDto $filter, ?Builder $foundProdIds): Builder
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        // every language row is searched, so a real name matches in any of them
        $query = $this->db->table($authorTable);

        $name = $filter->name !== null ? trim($filter->name) : '';
        if ($name !== '') {
            // a backslash escapes the wildcards in MariaDB
            $pattern = '%' . addcslashes($name, '\\%_') . '%';
            $query->where(static function (Builder $nameQuery) use ($authorTable, $aliasTable, $pattern): void {
                $nameQuery->where($authorTable . '.title', 'like', $pattern)
                    ->orWhere($authorTable . '.realName', 'like', $pattern)
                    ->orWhereIn($authorTable . '.id', static function (Builder $aliasQuery) use ($aliasTable, $pattern): void {
                        $aliasQuery->select('authorId')->from($aliasTable)->where('title', 'like', $pattern);
                    });
            });
        }
        if ($filter->countryIds !== []) {
            $query->whereIn($authorTable . '.country', $filter->countryIds);
        }
        if ($filter->cityIds !== []) {
            $query->whereIn($authorTable . '.city', $filter->cityIds);
        }
        if ($filter->groupIds !== []) {
            $this->whereRecorded($query, $this->memberships($filter->groupIds));
        }
        if ($filter->activeFrom !== null || $filter->activeTo !== null) {
            $this->whereRecorded($query, $this->credits($this->prodsOfYears($filter->activeFrom, $filter->activeTo), []));
        }
        if ($foundProdIds !== null) {
            $this->whereRecorded($query, $this->credits((clone $foundProdIds)->select('id'), $filter->prodRoles));
        }
        if ($filter->prodIds !== []) {
            $this->whereRecorded($query, $this->credits($filter->prodIds, $filter->prodRoles));
        } elseif ($foundProdIds === null && $filter->prodRoles !== []) {
            $this->whereRecorded($query, $this->credits(null, $filter->prodRoles));
        }
        if ($filter->coAuthorId !== null) {
            $this->whereRecorded($query, $this->credits($this->prodsOf($filter->coAuthorId), []));
            $query->where($authorTable . '.id', '<>', $filter->coAuthorId);
        }

        return $query;
    }

    /**
     * The author is the one recorded, or the author of the alias recorded.
     *
     * @param Closure $recordedIds a query of `authorId` rows
     */
    private function whereRecorded(Builder $query, Closure $recordedIds): void
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        $query->where(static function (Builder $recordedQuery) use ($authorTable, $aliasTable, $recordedIds): void {
            $recordedQuery->whereIn($authorTable . '.id', $recordedIds)
                ->orWhereIn($authorTable . '.id', static function (Builder $aliasQuery) use ($aliasTable, $recordedIds): void {
                    $aliasQuery->select('authorId')->from($aliasTable)->whereIn('id', $recordedIds);
                });
        });
    }

    /**
     * Members recorded in the groups or their aliases.
     *
     * @param int[] $groupIds
     */
    private function memberships(array $groupIds): Closure
    {
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $groupAliasTable = $this->tableName(DatabaseTable::GroupAlias);

        return static function (Builder $memberQuery) use ($authorshipTable, $groupAliasTable, $groupIds): void {
            $memberQuery->select('authorId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Group->value)
                ->where(static function (Builder $groupQuery) use ($groupAliasTable, $groupIds): void {
                    $groupQuery->whereIn('elementId', $groupIds)
                        ->orWhereIn('elementId', static function (Builder $aliasQuery) use ($groupAliasTable, $groupIds): void {
                            $aliasQuery->select('id')->from($groupAliasTable)->whereIn('groupId', $groupIds);
                        });
                });
        };
    }

    /**
     * Who is credited on the productions (any when `null`), in one of the
     * roles when any are given.
     *
     * @param int[]|Builder|Closure|null $prodIds
     * @param string[] $roles
     */
    private function credits(array|Builder|Closure|null $prodIds, array $roles): Closure
    {
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);

        return static function (Builder $creditQuery) use ($authorshipTable, $prodIds, $roles): void {
            $creditQuery->select('authorId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Prod->value);
            if ($prodIds !== null) {
                $creditQuery->whereIn('elementId', $prodIds);
            }
            AuthorshipRoleCondition::apply($creditQuery, $roles);
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

    /**
     * Productions credited to the author or one of its aliases.
     */
    private function prodsOf(int $authorId): Closure
    {
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);

        return static function (Builder $prodQuery) use ($authorshipTable, $aliasTable, $authorId): void {
            $prodQuery->select('elementId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Prod->value)
                ->where(static function (Builder $authorQuery) use ($aliasTable, $authorId): void {
                    $authorQuery->where('authorId', '=', $authorId)
                        ->orWhereIn('authorId', static function (Builder $aliasQuery) use ($aliasTable, $authorId): void {
                            $aliasQuery->select('id')->from($aliasTable)->where('authorId', '=', $authorId);
                        });
                });
        };
    }
}
