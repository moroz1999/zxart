<?php
declare(strict_types=1);

namespace ZxArt\Releases\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;

readonly final class ReleasesRepository
{
    public const TABLE = 'module_zxrelease';

    public function __construct(
        private Connection $db,
    )
    {
    }

    /**
     * @return int[]
     */
    public function getLatestAddedIds(int $limit): array
    {
        /** @var int[] $ids */
        $ids = $this->db->table(self::TABLE . ' AS releases')
            ->select('releases.id')
            ->leftJoin('structure_elements AS se', 'se.id', '=', 'releases.id')
            // a release is reachable only through its production, which needs a category
            ->whereIn('releases.id', static function (Builder $subQuery): void {
                $subQuery->from('structure_links AS parent_links')
                    ->join('structure_links AS category_links', 'category_links.childStructureId', '=', 'parent_links.parentStructureId')
                    ->select('parent_links.childStructureId')
                    ->where('parent_links.type', '=', LinkTypes::STRUCTURE->value)
                    ->where('category_links.type', '=', LinkTypes::ZX_PROD_CATEGORY->value);
            })
            ->orderBy('se.dateCreated', 'desc')
            ->limit($limit)
            ->pluck('releases.id');

        return $ids;
    }
}
