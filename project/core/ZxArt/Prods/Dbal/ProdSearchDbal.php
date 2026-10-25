<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\EntityType;

/**
 * Narrows the productions a catalogue query left (`$catalogueIds`, a query
 * of rows with an `id`) by title, years, author and group, and pages them by
 * title.
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
    public function findIds(
        Builder $catalogueIds,
        ?string $title,
        ?int $yearFrom,
        ?int $yearTo,
        ?int $authorId,
        ?int $groupId,
        int $start,
        int $limit,
    ): array {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        /** @var int[] $ids */
        $ids = $this->buildQuery($catalogueIds, $title, $yearFrom, $yearTo, $authorId, $groupId)
            ->select([$prodTable . '.id', $prodTable . '.title'])
            ->distinct()
            ->orderBy($prodTable . '.title')
            ->orderBy($prodTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(
        Builder $catalogueIds,
        ?string $title,
        ?int $yearFrom,
        ?int $yearTo,
        ?int $authorId,
        ?int $groupId,
    ): int {
        return $this->buildQuery($catalogueIds, $title, $yearFrom, $yearTo, $authorId, $groupId)
            ->distinct()
            ->count($this->tableName(DatabaseTable::ZxProd) . '.id');
    }

    private function buildQuery(
        Builder $catalogueIds,
        ?string $title,
        ?int $yearFrom,
        ?int $yearTo,
        ?int $authorId,
        ?int $groupId,
    ): Builder {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $query = $this->db->table($prodTable)->whereIn($prodTable . '.id', (clone $catalogueIds)->select('id'));

        if ($title !== null && $title !== '') {
            // a backslash escapes the wildcards in MariaDB
            $pattern = '%' . addcslashes($title, '\\%_') . '%';
            $query->where(static function (Builder $titleQuery) use ($prodTable, $pattern): void {
                $titleQuery->where($prodTable . '.title', 'like', $pattern)
                    ->orWhere($prodTable . '.altTitle', 'like', $pattern);
            });
        }
        if ($yearFrom !== null) {
            $query->where($prodTable . '.year', '>=', $yearFrom);
        }
        if ($yearTo !== null) {
            $query->where($prodTable . '.year', '<=', $yearTo);
        }
        if ($authorId !== null) {
            $query->whereIn($prodTable . '.id', $this->prodsOfAuthor($authorId));
        }
        if ($groupId !== null) {
            $query->whereIn($prodTable . '.id', $this->prodsOfGroup($groupId));
        }

        return $query;
    }

    /**
     * Credited on the production itself, as the author or one of its aliases.
     */
    private function prodsOfAuthor(int $authorId): \Closure
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

    /**
     * Made or published by the group or one of its aliases.
     */
    private function prodsOfGroup(int $groupId): \Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $aliasTable = $this->tableName(DatabaseTable::GroupAlias);

        return static function (Builder $prodQuery) use ($linksTable, $aliasTable, $groupId): void {
            $prodQuery->select('childStructureId')
                ->from($linksTable)
                ->whereIn('type', [LinkTypes::ZX_PROD_GROUPS->value, LinkTypes::ZX_PROD_PUBLISHERS->value])
                ->where(static function (Builder $groupQuery) use ($aliasTable, $groupId): void {
                    $groupQuery->where('parentStructureId', '=', $groupId)
                        ->orWhereIn('parentStructureId', static function (Builder $aliasQuery) use ($aliasTable, $groupId): void {
                            $aliasQuery->select('id')->from($aliasTable)->where('groupId', '=', $groupId);
                        });
                });
        };
    }
}
