<?php
declare(strict_types=1);


namespace ZxArt\Pictures\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\Shared\SortingParams;

readonly final class PicturesRepository
{
    public const TABLE = 'module_zxpicture';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findPicturesByTitle(string $title): array
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');

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
    public function getBestOfMonthIds(int $limit, int $year): array
    {
        $thirtyDaysAgo = time() - (30 * 86400);

        return $this->getSelectSql()
            ->where('dateAdded', '>=', $thirtyDaysAgo)
            ->where('year', '=', $year)
            ->orderBy('votes', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getUnvotedByUserIds(int $userId, int $limit, int $topN): array
    {
        return $this->getSelectSql()
            ->where('denyVoting', '=', 0)
            ->whereNotIn('id', function ($subQuery) use ($userId) {
                $subQuery->select('votes_history.elementId')
                    ->from('votes_history')
                    ->where('votes_history.type', '=', 'zxPicture')
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

    /**
     * Returns IDs of pictures linked to the given author (including all its aliases)
     * via structure_links with type 'authorPicture'.
     *
     * @return int[]
     */
    public function findIdsByAuthorId(int $authorId): array
    {
        return $this->db->table(self::TABLE)
            ->select(self::TABLE . '.id')
            ->join(
                'structure_links',
                fn($join) => $join
                    ->on('structure_links.childStructureId', '=', self::TABLE . '.id')
                    ->where('structure_links.type', '=', 'authorPicture')
            )
            ->where(function ($q) use ($authorId) {
                $q->where('structure_links.parentStructureId', '=', $authorId)
                    ->orWhereIn(
                        'structure_links.parentStructureId',
                        fn($sub) => $sub->select('id')->from('module_authoralias')->where('authorId', '=', $authorId)
                    );
            })
            ->distinct()
            ->orderBy(self::TABLE . '.title')
            ->pluck(self::TABLE . '.id');
    }

    /**
     * @return int[]
     */
    public function findPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        return $this->db->table(self::TABLE)
            ->join(
                'structure_links',
                fn($join) => $join
                    ->on('structure_links.childStructureId', '=', self::TABLE . '.id')
                    ->where('structure_links.type', '=', $linkType)
                    ->where('structure_links.parentStructureId', '=', $elementId)
            )
            ->orderBy(self::TABLE . '.' . $sorting->column, $sorting->direction->value)
            ->offset($start)
            ->limit($limit)
            ->pluck(self::TABLE . '.id');
    }

    public function countByLinkedElement(int $elementId, string $linkType): int
    {
        return (int)$this->db->table(self::TABLE)
            ->join(
                'structure_links',
                fn($join) => $join
                    ->on('structure_links.childStructureId', '=', self::TABLE . '.id')
                    ->where('structure_links.type', '=', $linkType)
                    ->where('structure_links.parentStructureId', '=', $elementId)
            )
            ->count();
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
