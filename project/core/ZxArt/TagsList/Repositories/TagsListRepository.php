<?php

declare(strict_types=1);

namespace ZxArt\TagsList\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Resolves tag usage amounts within a collection section directly from the
 * structure-link table.
 */
readonly final class TagsListRepository extends AbstractRepository
{
    private const string LINKS_ALIAS = 'links';
    private const string ITEMS_ALIAS = 'items';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return array<int, int> Tag id to section-specific usage amount.
     */
    public function getSectionTagAmounts(DatabaseTable $itemsTable, int $minimumAmount): array
    {
        $linksTable = $this->tableAlias(DatabaseTable::StructureLinks, self::LINKS_ALIAS);
        $itemsTableName = $this->tableAlias($itemsTable, self::ITEMS_ALIAS);

        $query = $this->db->table($linksTable)
            ->join(
                $itemsTableName,
                self::ITEMS_ALIAS . '.id',
                '=',
                self::LINKS_ALIAS . '.childStructureId',
            )
            ->where(self::LINKS_ALIAS . '.type', '=', LinkTypes::TAG->value);
        $this->groupBy($query, self::LINKS_ALIAS . '.parentStructureId');
        $query->orderBy(self::LINKS_ALIAS . '.parentStructureId');

        /** @var list<int|string> $ids */
        $ids = (clone $query)->pluck(self::LINKS_ALIAS . '.parentStructureId');

        $amountsQuery = clone $query;
        $amountsQuery->distinct();
        $amountsQuery->aggregate = [
            'function' => 'count',
            'columns' => [self::LINKS_ALIAS . '.childStructureId'],
        ];

        /** @var list<array{aggregate: int|string}> $rows */
        $rows = $amountsQuery->get();

        $amounts = [];
        foreach ($ids as $index => $id) {
            $amount = (int)($rows[$index]['aggregate'] ?? 0);
            if ($amount >= $minimumAmount) {
                $amounts[(int)$id] = $amount;
            }
        }

        return $amounts;
    }

    private function groupBy(Builder $query, string $column): void
    {
        call_user_func_array([$query, 'groupBy'], [$column]);
    }

}
