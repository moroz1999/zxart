<?php

declare(strict_types=1);

namespace ZxArt\AuthorList\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

final readonly class AuthorListRepository
{
    private const string AUTHORS_TABLE = 'module_author';
    private const string ALIASES_TABLE = 'module_authoralias';
    private const string DEFAULT_SORT_COLUMN = 'title';
    private const string DEFAULT_SORT_DIRECTION = 'asc';
    private const array ALLOWED_SORT_COLUMNS = [
        'title',
        'graphicsRating',
        'musicRating',
        'id',
    ];
    private const array ALLOWED_SORT_DIRECTIONS = [
        'asc',
        'desc',
    ];

    private const array LATIN_TO_CYRILLIC = [
        'A' => 'А', 'B' => 'Б', 'C' => 'Ц', 'D' => 'Д', 'E' => 'Е',
        'F' => 'Ф', 'G' => 'Г', 'H' => 'Х', 'I' => 'И', 'J' => 'Й',
        'K' => 'К', 'L' => 'Л', 'M' => 'М', 'N' => 'Н', 'O' => 'О',
        'P' => 'П', 'R' => 'Р', 'S' => 'С', 'T' => 'Т', 'U' => 'У',
        'V' => 'В', 'W' => 'В', 'X' => 'Х', 'Y' => 'У', 'Z' => 'З',
    ];

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
        string $sortColumn,
        string $sortDirection,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = ['author', 'authorAlias'],
    ): array {
        $queries = $this->buildTypedQueries($types, $search, $countryId, $cityId, $letter);
        if ($queries === []) {
            return [];
        }

        $normalizedSortColumn = $this->normalizeSortColumn($sortColumn);
        $normalizedSortDirection = $this->normalizeSortDirection($sortDirection);

        $combined = array_shift($queries);
        foreach ($queries as $q) {
            $combined->unionAll($q);
        }

        return $combined
            ->orderBy($normalizedSortColumn, $normalizedSortDirection)
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
    ): int {
        $total = 0;

        if (in_array('author', $types, true)) {
            $total += $this->buildAuthorsQuery($search, $countryId, $cityId, $letter)->count(self::AUTHORS_TABLE . '.id');
        }

        if (in_array('authorAlias', $types, true)) {
            $total += $this->buildAliasesQuery($search, $countryId, $cityId, $letter)->count(self::ALIASES_TABLE . '.id');
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
    ): array {
        $queries = [];

        if (in_array('author', $types, true)) {
            $queries[] = $this->buildAuthorsQuery($search, $countryId, $cityId, $letter);
        }

        if (in_array('authorAlias', $types, true)) {
            $queries[] = $this->buildAliasesQuery($search, $countryId, $cityId, $letter);
        }

        return $queries;
    }

    /**
     * @return int[]
     */
    public function findCountryIds(?string $letter = null): array
    {
        $authorsQuery = $this->db->table(self::AUTHORS_TABLE)
            ->distinct()
            ->where(self::AUTHORS_TABLE . '.country', '>', 0)
            ->select(self::AUTHORS_TABLE . '.country');
        $this->applyLetterFilter($authorsQuery, self::AUTHORS_TABLE . '.title', $letter);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::AUTHORS_TABLE, self::AUTHORS_TABLE . '.id', '=', self::ALIASES_TABLE . '.authorId')
            ->where(self::AUTHORS_TABLE . '.country', '>', 0)
            ->select(self::AUTHORS_TABLE . '.country');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);

        $authorsQuery->unionAll($aliasesQuery);

        return $authorsQuery->pluck('country');
    }

    /**
     * @return int[]
     */
    public function findCityIds(?string $letter = null): array
    {
        $authorsQuery = $this->db->table(self::AUTHORS_TABLE)
            ->distinct()
            ->where(self::AUTHORS_TABLE . '.city', '>', 0)
            ->select(self::AUTHORS_TABLE . '.city');
        $this->applyLetterFilter($authorsQuery, self::AUTHORS_TABLE . '.title', $letter);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::AUTHORS_TABLE, self::AUTHORS_TABLE . '.id', '=', self::ALIASES_TABLE . '.authorId')
            ->where(self::AUTHORS_TABLE . '.city', '>', 0)
            ->select(self::AUTHORS_TABLE . '.city');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);

        $authorsQuery->unionAll($aliasesQuery);

        return $authorsQuery->pluck('city');
    }

    private function buildAuthorsQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
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

        return $query;
    }

    private function buildAliasesQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
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

        return $query;
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

        $upper = mb_strtoupper($letter);
        $letters = [$upper];

        if (isset(self::LATIN_TO_CYRILLIC[$upper])) {
            $letters[] = self::LATIN_TO_CYRILLIC[$upper];
        } else {
            $reverse = array_search($upper, self::LATIN_TO_CYRILLIC, true);
            if ($reverse !== false) {
                $letters[] = $reverse;
            }
        }

        $query->where(function (Builder $q) use ($titleColumn, $letters) {
            foreach ($letters as $l) {
                $q->orWhere($titleColumn, 'like', $l . '%');
            }
        });
    }

    private function normalizeSortColumn(string $sortColumn): string
    {
        if (in_array($sortColumn, self::ALLOWED_SORT_COLUMNS, true)) {
            return $sortColumn;
        }

        return self::DEFAULT_SORT_COLUMN;
    }

    private function normalizeSortDirection(string $sortDirection): string
    {
        if (in_array($sortDirection, self::ALLOWED_SORT_DIRECTIONS, true)) {
            return $sortDirection;
        }

        return self::DEFAULT_SORT_DIRECTION;
    }
}
