<?php
declare(strict_types=1);


namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;

final class ProdsRepository
{
    public const TABLE = 'module_zxprod';

    public function __construct(
        private readonly Connection               $db,
        private readonly AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function getProdByTitleAndYear(?string $title, ?int $year): ?int
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByTitle($query, $title, 'title');

        $query->where('year', '=', $year);
        return $query->value('id');
    }

    public function getProdByTitle(?string $title): ?int
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByTitle($query, $title, 'title');

        if ($id = $query->value('id')) {
            return $id;
        }

        return null;
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
