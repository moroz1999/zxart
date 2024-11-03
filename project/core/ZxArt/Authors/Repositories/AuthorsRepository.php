<?php
declare(strict_types=1);


namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;

final class AuthorsRepository
{
    private const TABLE = 'module_author';

    public function __construct(
        private readonly Connection $db,
    )
    {

    }

    public function findAuthorIdsByName(?string $name = null, ?string $realName = null): ?array
    {
        if ($name === null && $realName === null) {
            return null;
        }

        $query = $this->db->table(self::TABLE)->select(['id']);

        if ($name !== null) {
            $name = trim($name);
            $encodedName = htmlentities($name, ENT_QUOTES);
            $query->orWhere('realName', 'like', $name)
                ->orWhere('realName', 'like', $encodedName)
                ->orWhere('title', 'like', $name)
                ->orWhere('title', 'like', $encodedName);
        }
        if ($realName !== null) {
            $realName = trim($realName);
            $encodedRealName = htmlentities($realName, ENT_QUOTES);

            $query->orWhere('title', 'like', $realName)
                ->orWhere('title', 'like', $encodedRealName)
                ->orWhere('realName', 'like', $realName)
                ->orWhere('realName', 'like', $encodedRealName);
        }
        if ($ids = $query->pluck('id')) {
            return $ids;
        }
        return null;
    }
}