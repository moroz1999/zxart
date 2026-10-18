<?php

declare(strict_types=1);

namespace ZxArt\Ratings\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * @psalm-api
 */
final readonly class VotesHistoryRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    public function deleteByElementId(int $elementId): void
    {
        $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->where('elementId', '=', $elementId)
            ->delete();
    }
}
