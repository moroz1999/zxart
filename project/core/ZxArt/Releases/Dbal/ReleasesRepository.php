<?php
declare(strict_types=1);

namespace ZxArt\Releases\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;

/**
 * @psalm-api
 */
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

    /**
     * Every release under the production, reachable or not.
     */
    public function countByProdId(int $prodId): int
    {
        return $this->db->table(self::TABLE . ' AS releases')
            ->join('structure_links AS links', 'links.childStructureId', '=', 'releases.id')
            ->where('links.parentStructureId', '=', $prodId)
            ->where('links.type', '=', LinkTypes::STRUCTURE->value)
            ->count();
    }

    /**
     * @return int[]
     */
    public function findIdsByProdId(int $prodId): array
    {
        /** @var int[] $ids */
        $ids = $this->db->table(self::TABLE . ' AS releases')
            ->join(DatabaseTable::StructureLinks->value . ' AS links', 'links.childStructureId', '=', 'releases.id')
            ->where('links.parentStructureId', '=', $prodId)
            ->where('links.type', '=', LinkTypes::STRUCTURE->value)
            ->distinct()
            ->orderBy('releases.id')
            ->pluck('releases.id');

        return $ids;
    }

    /**
     * @return int[]
     */
    public function findIdsByTitle(string $phrase, int $limit): array
    {
        return $this->findIdsContaining('title', $phrase, $limit);
    }

    /**
     * @return int[]
     */
    public function findIdsByFileName(string $phrase, int $limit): array
    {
        return $this->findIdsContaining('fileName', $phrase, $limit);
    }

    /**
     * Releases holding a file of that md5: the release file itself or any file
     * inside it.
     *
     * @return int[]
     */
    public function findIdsByMd5(string $md5): array
    {
        /** @var int[] $ids */
        $ids = $this->db->table(DatabaseTable::FilesRegistry->value)
            ->where('md5', '=', $md5)
            ->distinct()
            ->orderBy('elementId')
            ->pluck('elementId');

        return $ids;
    }

    /**
     * @return int[]
     */
    private function findIdsContaining(string $column, string $phrase, int $limit): array
    {
        /** @var int[] $ids */
        $ids = $this->db->table(self::TABLE)
            // a backslash escapes the wildcards in MariaDB
            ->where($column, 'like', '%' . addcslashes($phrase, '\\%_') . '%')
            ->distinct()
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }
}
