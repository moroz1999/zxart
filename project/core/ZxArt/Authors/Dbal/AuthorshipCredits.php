<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dbal;

use Closure;
use Illuminate\Database\Query\Builder;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;

/**
 * Narrows a query to the elements of a type credited to authors in the
 * authorship records. An author takes in its aliases.
 */
final class AuthorshipCredits
{
    /**
     * Every author must be credited, each in one of the roles when roles are
     * given; with `$anyAuthor` one of them is enough. Roles alone find what
     * anyone is credited with in them; neither leaves the query as it is.
     *
     * @param string $idColumn the column of the element id in `$query`
     * @param int[] $authorIds
     * @param string[] $roles
     */
    public static function apply(Builder $query, string $idColumn, EntityType $type, array $authorIds, bool $anyAuthor, array $roles): void
    {
        if ($authorIds === []) {
            if ($roles !== []) {
                $query->whereIn($idColumn, self::credited($type, null, $roles));
            }
            return;
        }
        if ($anyAuthor) {
            $query->whereIn($idColumn, self::credited($type, $authorIds, $roles));
            return;
        }
        foreach ($authorIds as $authorId) {
            $query->whereIn($idColumn, self::credited($type, [$authorId], $roles));
        }
    }

    /**
     * The elements credited to the authors (or their aliases), or to anyone
     * when no author is given, in one of the roles when any are given.
     *
     * @param int[]|null $authorIds
     * @param string[] $roles
     */
    private static function credited(EntityType $type, ?array $authorIds, array $roles): Closure
    {
        $authorshipTable = DatabaseTable::Authorship->value;
        $aliasTable = DatabaseTable::AuthorAlias->value;

        return static function (Builder $creditQuery) use ($authorshipTable, $aliasTable, $type, $authorIds, $roles): void {
            $creditQuery->select('elementId')
                ->from($authorshipTable)
                ->where('type', '=', $type->value);
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
}
