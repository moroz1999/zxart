<?php

declare(strict_types=1);

namespace ZxArt\Search\Dbal;

use Illuminate\Database\Query\Builder;

/**
 * How every search compares a title with a phrase: by their keys — lower
 * case, letters and digits of any script only, HTML entities left out — so
 * `rebel-star` finds `Rebelstar` and a title stored HTML-encoded is matched by
 * what it reads. A phrase without a letter or a digit matches nothing.
 *
 * The query builder has no regular expressions, so this is the one place the
 * key of a column is written as an SQL expression; the column is wrapped by
 * the grammar and every value is bound.
 */
final readonly class TitleMatch
{
    private const string ENTITY_PATTERN = '&(#[0-9]+|#[xX][0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);';
    private const string NOT_KEY_PATTERN = '[^\p{L}\p{N}]';

    public function key(string $text): string
    {
        $withoutEntities = (string)preg_replace('/' . self::ENTITY_PATTERN . '/u', '', $text);

        return (string)preg_replace('/' . self::NOT_KEY_PATTERN . '/u', '', mb_strtolower($withoutEntities));
    }

    /**
     * Narrows the query to the rows whose key of any of the columns matches the phrase.
     *
     * @param non-empty-list<string> $columns
     */
    public function where(Builder $query, array $columns, string $phrase, TitleMatchMode $mode = TitleMatchMode::Contains): Builder
    {
        $key = $this->key($phrase);
        if ($key === '') {
            return $query->whereRaw('1 = 0');
        }
        $operator = $mode === TitleMatchMode::Exact ? '=' : 'like';
        $value = $mode === TitleMatchMode::Exact ? $key : '%' . $key . '%';

        return $query->where(function (Builder $columnsQuery) use ($columns, $operator, $value): void {
            foreach ($columns as $column) {
                [$keySql, $keyBindings] = $this->keySql($columnsQuery, $column);
                $columnsQuery->orWhereRaw($keySql . ' ' . $operator . ' ?', [...$keyBindings, $value]);
            }
        });
    }

    /**
     * Orders a query grouped by the element: the key of the column equal to
     * the phrase's first, then starting with it, then the rest.
     */
    public function orderGroupsByCloseness(Builder $query, string $column, string $phrase): Builder
    {
        $key = $this->key($phrase);
        [$keySql, $keyBindings] = $this->keySql($query, $column);

        return $query->orderByRaw(
            'MIN(CASE WHEN ' . $keySql . ' = ? THEN 0 WHEN ' . $keySql . ' LIKE ? THEN 1 ELSE 2 END)',
            [...$keyBindings, $key, ...$keyBindings, $key . '%'],
        );
    }

    /**
     * Orders a query grouped by the element by its title as stored.
     */
    public function orderGroupsByTitle(Builder $query, string $column): Builder
    {
        return $query->orderByRaw('MIN(' . $query->getGrammar()->wrap($column) . ')');
    }

    /**
     * @return array{string, list<string>}
     */
    private function keySql(Builder $query, string $column): array
    {
        $wrapped = $query->getGrammar()->wrap($column);

        return [
            'REGEXP_REPLACE(LOWER(REGEXP_REPLACE(' . $wrapped . ', ?, \'\')), ?, \'\')',
            [self::ENTITY_PATTERN, self::NOT_KEY_PATTERN],
        ];
    }
}
