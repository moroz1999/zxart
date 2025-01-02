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

    public function findProdsByTitles(string $title): array
    {
        $theTitle = 'The ' . $title;

        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');

        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'altTitle');
        $query->orWhere('altTitle', 'like', $title);
        $query->orWhere('altTitle', 'like', $title . '%');

        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $theTitle, 'title');
        $query->orWhere('title', 'like', $theTitle);
        $query->orWhere('title', 'like', $theTitle . '%');

        return $query->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
