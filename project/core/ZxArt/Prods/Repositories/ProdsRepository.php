<?php
declare(strict_types=1);


namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;

readonly final class ProdsRepository
{
    public const TABLE = 'module_zxprod';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findProdsByTitle(string $title): array
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByTitle($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');

        return $query->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
