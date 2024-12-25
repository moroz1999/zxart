<?php
declare(strict_types=1);


namespace ZxArt\Tunes\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;

readonly final class TunesRepository
{
    public const TABLE = 'module_zxmusic';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findTunesByTitle(string $title): array
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByTitle($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');
        $query->orWhere('internalTitle', 'like', $title);
        $query->orWhere('internalTitle', 'like', $title . '%');

        return $query->pluck('id');
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

}
