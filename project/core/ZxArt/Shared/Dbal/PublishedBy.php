<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dbal;

use Closure;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;

/**
 * The elements published by authors or groups. A publisher takes in its
 * aliases.
 */
final class PublishedBy
{
    /**
     * A query of the ids of the elements the publisher links of the type hold.
     *
     * @param int[] $publisherIds authors or groups
     */
    public static function elements(LinkTypes $publisherLinks, array $publisherIds): Closure
    {
        $linksTable = DatabaseTable::StructureLinks->value;
        $authorAliasTable = DatabaseTable::AuthorAlias->value;
        $groupAliasTable = DatabaseTable::GroupAlias->value;

        return static function (Builder $linkQuery) use ($linksTable, $authorAliasTable, $groupAliasTable, $publisherLinks, $publisherIds): void {
            $linkQuery->select('childStructureId')
                ->from($linksTable)
                ->where('type', '=', $publisherLinks->value)
                ->where(static function (Builder $publisherQuery) use ($authorAliasTable, $groupAliasTable, $publisherIds): void {
                    $publisherQuery->whereIn('parentStructureId', $publisherIds)
                        ->orWhereIn('parentStructureId', static function (Builder $aliasQuery) use ($authorAliasTable, $publisherIds): void {
                            $aliasQuery->select('id')->from($authorAliasTable)->whereIn('authorId', $publisherIds);
                        })
                        ->orWhereIn('parentStructureId', static function (Builder $aliasQuery) use ($groupAliasTable, $publisherIds): void {
                            $aliasQuery->select('id')->from($groupAliasTable)->whereIn('groupId', $publisherIds);
                        });
                });
        };
    }
}
