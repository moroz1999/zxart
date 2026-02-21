<?php

declare(strict_types=1);

namespace ZxArt\Comments\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

readonly final class CommentsRepository
{
    private const string TABLE = 'structure_elements';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Returns the total number of comments.
     */
    public function countAll(): int
    {
        return $this->getCommentQuery()->count('id');
    }

    /**
     * Returns comment IDs for a given page, sorted by date descending.
     *
     * @return int[]
     */
    public function getIdsPaginated(int $offset, int $limit): array
    {
        $rows = $this->getCommentQuery()
            ->orderBy('dateCreated', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->select('id')
            ->get();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    /**
     * Returns the latest comment IDs sorted by date descending.
     *
     * @return int[]
     */
    public function getLatestIds(int $limit): array
    {
        $rows = $this->getCommentQuery()
            ->where('dateCreated', '<=', time())
            ->orderBy('dateCreated', 'desc')
            ->limit($limit)
            ->select('id')
            ->get();

        return array_map(static fn(array $row): int => (int)$row['id'], $rows);
    }

    private function getCommentQuery(): Builder
    {
        return $this->db->table(self::TABLE)
            ->where('structureType', '=', 'comment');
    }
}
