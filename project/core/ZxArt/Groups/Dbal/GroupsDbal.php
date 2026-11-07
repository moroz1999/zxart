<?php
declare(strict_types=1);


namespace ZxArt\Groups\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Search\Dbal\TitleMatch;
use ZxArt\Search\Dbal\TitleMatchMode;

final class GroupsDbal
{
    private const TABLE = 'module_group';

    public function __construct(
        private readonly Connection               $db,
        private readonly TitleMatch $titleMatch,
    )
    {
    }

    public function findGroupIdsByName(?string $name = null): ?array
    {
        if ($name === null) {
            return null;
        }

        $query = $this->db->table(self::TABLE)->select(['id']);
        $query = $query->orWhere(fn(Builder $exactQuery): Builder => $this->titleMatch->where($exactQuery, ['title'], $name, TitleMatchMode::Exact));
        $query = $query->orWhere(fn(Builder $exactQuery): Builder => $this->titleMatch->where($exactQuery, ['abbreviation'], $name, TitleMatchMode::Exact));
        $query->orWhere('title', 'LIKE', $name . '%');

        if ($ids = $query->pluck('id')) {
            return $ids;
        }
        return null;
    }
}