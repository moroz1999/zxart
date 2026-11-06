<?php

declare(strict_types=1);

namespace ZxArt\Users\Dbal;

use Illuminate\Database\Connection;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\DatabaseTable;

/**
 * Whether a sign-in name or an email already belongs to someone.
 */
final readonly class UserAccountsDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    public function isUserNameTaken(string $userName, int $exceptUserId): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::User))
            ->where('userName', '=', $userName)
            ->where('id', '!=', $exceptUserId)
            ->exists();
    }

    public function isEmailTaken(string $email, int $exceptUserId): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::User))
            ->where('email', '=', $email)
            ->where('id', '!=', $exceptUserId)
            ->exists();
    }
}
