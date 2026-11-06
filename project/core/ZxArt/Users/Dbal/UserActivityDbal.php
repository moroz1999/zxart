<?php

declare(strict_types=1);

namespace ZxArt\Users\Dbal;

use Illuminate\Database\Connection;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\DatabaseTable;

/**
 * What a user has left on the site, as the management screen counts it.
 */
final readonly class UserActivityDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    public function countComments(int $userId): int
    {
        return $this->db->table($this->tableName(DatabaseTable::Comment))->where('userId', '=', $userId)->count();
    }

    public function countVotes(int $userId): int
    {
        return $this->db->table($this->tableName(DatabaseTable::VotesHistory))->where('userId', '=', $userId)->count();
    }
}
