<?php
declare(strict_types=1);


namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final class ProdsRepository
{
    public const TABLE = 'module_zxprod';

    public function __construct(
        private readonly Connection $db,
    )
    {

    }

    public function getProdByTitleAndYear(?string $title, ?int $year): ?int
    {
        $query = $this->getSelectSql();
        $query = $this->addSearchByTitle($query, $title);

        $query->where('year', '=', $year);
        return $query->value('id');
    }

    public function getProdByTitle(?string $title): ?int
    {
        $query = $this->getSelectSql();
        $query = $this->addSearchByTitle($query, $title);

        if ($id = $query->value('id')) {
            return $id;
        }

        return null;
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

    private function addSearchByTitle(Builder $query, ?string $title): Builder
    {
        if ($title === null) {
            return $query;
        }

        $alphanumericTitle = $this->toAlphanumeric($title);
        $query->whereRaw("REGEXP_REPLACE(LOWER(title), '[^a-z0-9а-я]', '') = ?", [$alphanumericTitle]);

        return $query;
    }

    private function toAlphanumeric(string $input): string
    {
        return preg_replace('/[^\p{L}\p{N}]/u', '', mb_strtolower($input));
    }
}
