<?php

declare(strict_types=1);

namespace ZxArt\TagsList\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

/**
 * Resolves the tag ids connected to a collection section (graphics, music, …)
 * directly from the structure-link table. Ordering, localization and usage
 * amounts are applied by the caller on the loaded tag elements.
 */
readonly final class TagsListRepository
{
    private const string LINKS_TABLE = 'structure_links';
    private const string TAG_LINK = 'tagLink';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Tag element ids linked (via tagLink) to at least one item in the given table.
     *
     * @return int[]
     */
    public function getSectionTagIds(string $itemsTable): array
    {
        $query = $this->db->table(self::LINKS_TABLE)
            ->where('type', '=', self::TAG_LINK)
            ->whereIn('childStructureId', function (Builder $sub) use ($itemsTable) {
                $sub->select('id')->from($itemsTable);
            })
            ->distinct();

        /** @var list<int|string> $ids */
        $ids = $query->pluck('parentStructureId');

        return array_map(static fn(int|string $id): int => (int)$id, $ids);
    }
}
