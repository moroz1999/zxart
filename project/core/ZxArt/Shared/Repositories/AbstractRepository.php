<?php

declare(strict_types=1);

namespace ZxArt\Shared\Repositories;

use ZxArt\Shared\DatabaseTable;

abstract readonly class AbstractRepository
{
    protected function tableName(DatabaseTable $table): string
    {
        return $table->value;
    }

    protected function tableColumn(DatabaseTable $table, string $column): string
    {
        return $this->tableName($table) . '.' . $column;
    }

    protected function tableAlias(DatabaseTable $table, string $alias): string
    {
        return $this->tableName($table) . ' as ' . $alias;
    }
}
