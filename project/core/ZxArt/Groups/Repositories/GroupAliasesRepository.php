<?php
declare(strict_types=1);


namespace ZxArt\Groups\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Helpers\AlphanumericColumnSearch;

final class GroupAliasesRepository
{
    private const TABLE = 'module_groupalias';

    public function __construct(
        private readonly Connection               $db,
        private readonly AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findAliasIdsByName(?string $name = null): ?array
    {
        if ($name === null) {
            return null;
        }

        $query = $this->db->table(self::TABLE)->select(['id']);
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $name, 'title');
        $query->orWhere('title', 'LIKE', $name . '%');

        if ($ids = $query->pluck('id')) {
            return $ids;
        }
        return null;
    }
}