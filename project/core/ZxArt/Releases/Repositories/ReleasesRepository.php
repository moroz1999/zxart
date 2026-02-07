<?php
declare(strict_types=1);

namespace ZxArt\Releases\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

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
        return $this->getSelectSql()
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }
}
