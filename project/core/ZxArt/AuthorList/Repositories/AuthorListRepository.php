<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\AuthorList\AuthorSortColumn;
use ZxArt\Shared\LatinCyrillicMap;
use ZxArt\Shared\SortDirection;

final readonly class AuthorListRepository
{
    private const string AUTHORS_TABLE = 'module_author';
    private const string ALIASES_TABLE = 'module_authoralias';

    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @param string[] $types Entity types to include ('author', 'authorAlias')
     * @return int[]
     */
    public function findPaged(
        int $start,
        int $limit,
        AuthorSortColumn $sortColumn,
        SortDirection $sortDirection,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = ['author', 'authorAlias'],
        ?string $items = null,
    ): array {
        $queries = $this->buildTypedQueries($types, $search, $countryId, $cityId, $letter, $items);
        if ($queries === []) {
            return [];
        }

        $combined = array_shift($queries);
        foreach ($queries as $q) {
            $combined->unionAll($q);
        }

        return $combined
            ->orderBy($sortColumn->value, $sortDirection->value)
            ->offset($start)
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @param string[] $types Entity types to include ('author', 'authorAlias')
     */
    public function count(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = ['author', 'authorAlias'],
        ?string $items = null,
    ): int {
        $total = 0;

        if (in_array('author', $types, true)) {
            $total += $this->buildAuthorsQuery($search, $countryId, $cityId, $letter, $items)->count(self::AUTHORS_TABLE . '.id');
        }

        if (in_array('authorAlias', $types, true)) {
            $total += $this->buildAliasesQuery($search, $countryId, $cityId, $letter, $items)->count(self::ALIASES_TABLE . '.id');
        }

        return $total;
    }

    /**
     * @param string[] $types
     * @return Builder[]
     */
    private function buildTypedQueries(
        array $types,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter,
        ?string $items = null,
    ): array {
        $queries = [];

        if (in_array('author', $types, true)) {
            $queries[] = $this->buildAuthorsQuery($search, $countryId, $cityId, $letter, $items);
        }

        if (in_array('authorAlias', $types, true)) {
            $queries[] = $this->buildAliasesQuery($search, $countryId, $cityId, $letter, $items);
        }

        return $queries;
    }

    /**
     * @return int[]
     */
    public function findCountryIds(?string $letter = null, ?string $items = null): array
    {
        $authorsQuery = $this->db->table(self::AUTHORS_TABLE)
            ->distinct()
            ->where(self::AUTHORS_TABLE . '.country', '>', 0)
            ->select(self::AUTHORS_TABLE . '.country');
        $this->applyLetterFilter($authorsQuery, self::AUTHORS_TABLE . '.title', $letter);
        $this->applyItemsFilter($authorsQuery, self::AUTHORS_TABLE, $items);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::AUTHORS_TABLE, self::AUTHORS_TABLE . '.id', '=', self::ALIASES_TABLE . '.authorId')
            ->where(self::AUTHORS_TABLE . '.country', '>', 0)
            ->select(self::AUTHORS_TABLE . '.country');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);
        $this->applyItemsFilter($aliasesQuery, self::AUTHORS_TABLE, $items);

        $authorsQuery->unionAll($aliasesQuery);

        return $authorsQuery->pluck('country');
    }

    /**
     * @return int[]
     */
    public function findCityIds(?string $letter = null, ?string $items = null): array
    {
        $authorsQuery = $this->db->table(self::AUTHORS_TABLE)
            ->distinct()
            ->where(self::AUTHORS_TABLE . '.city', '>', 0)
            ->select(self::AUTHORS_TABLE . '.city');
        $this->applyLetterFilter($authorsQuery, self::AUTHORS_TABLE . '.title', $letter);
        $this->applyItemsFilter($authorsQuery, self::AUTHORS_TABLE, $items);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::AUTHORS_TABLE, self::AUTHORS_TABLE . '.id', '=', self::ALIASES_TABLE . '.authorId')
            ->where(self::AUTHORS_TABLE . '.city', '>', 0)
            ->select(self::AUTHORS_TABLE . '.city');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);
        $this->applyItemsFilter($aliasesQuery, self::AUTHORS_TABLE, $items);

        $authorsQuery->unionAll($aliasesQuery);

        return $authorsQuery->pluck('city');
    }

    private function buildAuthorsQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        ?string $items = null,
    ): Builder {
        $query = $this->db->table(self::AUTHORS_TABLE)
            ->distinct()
            ->select([
                self::AUTHORS_TABLE . '.id',
                self::AUTHORS_TABLE . '.title',
                self::AUTHORS_TABLE . '.graphicsRating',
                self::AUTHORS_TABLE . '.musicRating',
            ]);

        if ($search !== null && $search !== '') {
            $likeSearch = '%' . $search . '%';
            $query->where(function (Builder $q) use ($likeSearch) {
                $q->where(self::AUTHORS_TABLE . '.title', 'like', $likeSearch)
                    ->orWhere(self::AUTHORS_TABLE . '.realName', 'like', $likeSearch);
            });
        }

        if ($countryId !== null) {
            $query->where(self::AUTHORS_TABLE . '.country', '=', $countryId);
        }

        if ($cityId !== null) {
            $query->where(self::AUTHORS_TABLE . '.city', '=', $cityId);
        }

        $this->applyLetterFilter($query, self::AUTHORS_TABLE . '.title', $letter);
        $this->applyItemsFilter($query, self::AUTHORS_TABLE, $items);

        return $query;
    }

    private function buildAliasesQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        ?string $items = null,
    ): Builder {
        $authAlias = 'auth';

        $query = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(
                self::AUTHORS_TABLE . ' as ' . $authAlias,
                $authAlias . '.id',
                '=',
                self::ALIASES_TABLE . '.authorId'
            )
            ->select([
                self::ALIASES_TABLE . '.id',
                self::ALIASES_TABLE . '.title',
                $authAlias . '.graphicsRating',
                $authAlias . '.musicRating',
            ]);

        if ($search !== null && $search !== '') {
            $likeSearch = '%' . $search . '%';
            $query->where(self::ALIASES_TABLE . '.title', 'like', $likeSearch);
        }

        if ($countryId !== null) {
            $query->where($authAlias . '.country', '=', $countryId);
        }

        if ($cityId !== null) {
            $query->where($authAlias . '.city', '=', $cityId);
        }

        $this->applyLetterFilter($query, self::ALIASES_TABLE . '.title', $letter);
        $this->applyItemsFilter($query, $authAlias, $items);

        return $query;
    }

    private function applyItemsFilter(Builder $query, string $authorTableOrAlias, ?string $items): void
    {
        if ($items === null || $items === '' || $items === 'all') {
            return;
        }

        if ($items === 'music') {
            $query->where($authorTableOrAlias . '.tunesQuantity', '>', 0);
        } elseif ($items === 'graphics') {
            $query->where($authorTableOrAlias . '.picturesQuantity', '>', 0);
        }
    }

    private function applyLetterFilter(Builder $query, string $titleColumn, ?string $letter): void
    {
        if ($letter === null || $letter === '') {
            return;
        }

        if ($letter === '#') {
            $allPrefixes = [];
            foreach (range('A', 'Z') as $latin) {
                $allPrefixes[] = $latin;
                $allPrefixes[] = strtolower($latin);
            }
            foreach (['А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'] as $cyr) {
                $allPrefixes[] = $cyr;
                $allPrefixes[] = mb_strtolower($cyr);
            }
            $query->where(function (Builder $q) use ($titleColumn, $allPrefixes) {
                foreach ($allPrefixes as $prefix) {
                    $q->where($titleColumn, 'not like', $prefix . '%');
                }
            });
            return;
        }

        $letters = LatinCyrillicMap::getEquivalentLetters($letter);

        $query->where(function (Builder $q) use ($titleColumn, $letters) {
            foreach ($letters as $l) {
                $q->orWhere($titleColumn, 'like', $l . '%');
            }
        });
    }

}
