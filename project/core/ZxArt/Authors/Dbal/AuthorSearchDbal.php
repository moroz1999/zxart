<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\EntityType;

/**
 * Authors by a part of the nickname, of the real name in any language or of an
 * alias, by location and by group membership. An alias that matches answers
 * its author, so each author comes once and aliases never do.
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
     * @return int[]
     */
    public function findIds(?string $name, ?int $countryId, ?int $cityId, ?int $groupId, int $start, int $limit): array
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        /** @var int[] $ids */
        $ids = $this->buildQuery($name, $countryId, $cityId, $groupId)
            ->select([$authorTable . '.id', $authorTable . '.title'])
            ->distinct()
            ->orderBy($authorTable . '.title')
            ->orderBy($authorTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(?string $name, ?int $countryId, ?int $cityId, ?int $groupId): int
    {
        return $this->buildQuery($name, $countryId, $cityId, $groupId)
            ->distinct()
            ->count($this->tableName(DatabaseTable::Author) . '.id');
    }

    private function buildQuery(?string $name, ?int $countryId, ?int $cityId, ?int $groupId): Builder
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        // every language row is searched, so a real name matches in any of them
        $query = $this->db->table($authorTable);

        if ($name !== null && $name !== '') {
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
        if ($countryId !== null) {
            $query->where($authorTable . '.country', '=', $countryId);
        }
        if ($cityId !== null) {
            $query->where($authorTable . '.city', '=', $cityId);
        }
        if ($groupId !== null) {
            $this->applyGroupFilter($query, $groupId);
        }

        return $query;
    }

    /**
     * A member is recorded for the author or one of its aliases, in the group or
     * one of its aliases.
     */
    private function applyGroupFilter(Builder $query, int $groupId): void
    {
        $authorTable = $this->tableName(DatabaseTable::Author);
        $aliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        $groupAliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $memberIds = static function (Builder $memberQuery) use ($authorshipTable, $groupAliasTable, $groupId): void {
            $memberQuery->select('authorId')
                ->from($authorshipTable)
                ->where('type', '=', EntityType::Group->value)
                ->where(static function (Builder $groupQuery) use ($groupAliasTable, $groupId): void {
                    $groupQuery->where('elementId', '=', $groupId)
                        ->orWhereIn('elementId', static function (Builder $aliasQuery) use ($groupAliasTable, $groupId): void {
                            $aliasQuery->select('id')->from($groupAliasTable)->where('groupId', '=', $groupId);
                        });
                });
        };
        $query->where(static function (Builder $groupQuery) use ($authorTable, $aliasTable, $memberIds): void {
            $groupQuery->whereIn($authorTable . '.id', $memberIds)
                ->orWhereIn($authorTable . '.id', static function (Builder $aliasQuery) use ($aliasTable, $memberIds): void {
                    $aliasQuery->select('authorId')->from($aliasTable)->whereIn('id', $memberIds);
                });
        });
    }
}
