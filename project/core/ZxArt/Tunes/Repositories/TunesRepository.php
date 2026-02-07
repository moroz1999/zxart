<?php
declare(strict_types=1);


namespace ZxArt\Tunes\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;

readonly final class TunesRepository
{
    public const TABLE = 'module_zxmusic';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findTunesByTitle(string $title): array
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');
        $query->orWhere('internalTitle', 'like', $title);
        $query->orWhere('internalTitle', 'like', $title . '%');

        return $query->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getNewIds(int $limit): array
    {
        return $this->getSelectSql()
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getUnvotedByUserIds(int $userId, int $limit, int $topN): array
    {
        $topIds = $this->getSelectSql()
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        return $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->whereNotIn('id', function ($subQuery) use ($userId) {
                $subQuery->select('votes_history.elementId')
                    ->from('votes_history')
                    ->where('votes_history.type', '=', 'zxMusic')
                    ->where('votes_history.userId', '=', $userId);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getRandomGoodIds(int $limit, int $topN): array
    {
        $topIds = $this->getSelectSql()
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        return $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
