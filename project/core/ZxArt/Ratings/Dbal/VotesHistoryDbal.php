<?php

declare(strict_types=1);

namespace ZxArt\Ratings\Dbal;

use Illuminate\Database\Connection;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;

/**
 * @psalm-api
 */
final readonly class VotesHistoryDbal extends AbstractDbal
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
