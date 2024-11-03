<?php
declare(strict_types=1);


namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;

final class GroupAliasesRepository
{
    private const TABLE = 'module_group';

    public function __construct(
        private readonly Connection $db,
    )
    {

    }

    public function findAliasIdsByName(?string $name = null): ?array
    {
        if ($name === null) {
            return null;
        }

        $query = $this->db->table(self::TABLE)->select(['id']);

        $name = trim($name);
        $encodedName = htmlentities($name, ENT_QUOTES);
        $query
            ->where('title', 'like', $name)
            ->orWhere('title', 'like', $encodedName);

        if ($ids = $query->pluck('id')) {
            return $ids;
        }
        return null;
    }
}