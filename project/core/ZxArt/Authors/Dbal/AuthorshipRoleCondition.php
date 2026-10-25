<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dbal;

use Illuminate\Database\Query\Builder;

/**
 * Narrows authorship records to those holding one of the roles. The roles are
 * stored as a JSON list of plain words.
 */
final class AuthorshipRoleCondition
{
    /**
     * @param string[] $roles none leaves the query as it is
     */
    public static function apply(Builder $query, array $roles): void
    {
        if ($roles === []) {
            return;
        }
        $query->where(static function (Builder $roleQuery) use ($roles): void {
            foreach ($roles as $role) {
                $roleQuery->orWhere('roles', 'like', '%"' . addcslashes($role, '\\%_"') . '"%');
            }
        });
    }
}
