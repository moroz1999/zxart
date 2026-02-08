<?php
declare(strict_types=1);

namespace ZxArt\Releases\Repositories;

use Illuminate\Database\Connection;

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
        return $this->db->table(self::TABLE . ' AS releases')
            ->select('releases.id')
            ->leftJoin('structure_elements AS se', 'se.id', '=', 'releases.id')
            ->orderBy('se.dateCreated', 'desc')
            ->limit($limit)
            ->pluck('releases.id');
    }
}
