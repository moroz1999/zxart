<?php

declare(strict_types=1);

namespace ZxArt\GroupList\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\GroupList\GroupSortColumn;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\LatinCyrillicMap;
use ZxArt\Shared\SortDirection;

final readonly class GroupListRepository
{
    private const string GROUPS_TABLE = 'module_group';
    private const string ALIASES_TABLE = 'module_groupalias';

    public function __construct(
        private Connection $db,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @param EntityType[] $types Entity types to include
     * @return int[]
     */
    public function findPaged(
        int $start,
        int $limit,
        GroupSortColumn $sortColumn,
        SortDirection $sortDirection,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = [EntityType::Group, EntityType::GroupAlias],
        ?string $groupType = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): array {
        $queries = $this->buildTypedQueries($types, $search, $countryId, $cityId, $letter, $groupType, $north, $south, $east, $west);
        if ($queries === []) {
            return [];
        }

        $combined = array_shift($queries);
        foreach ($queries as $q) {
            $combined->unionAll($q);
        }

        /** @var int[] $ids */
        $ids = $combined
            ->orderBy($sortColumn->value, $sortDirection->value)
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    /**
     * @param EntityType[] $types Entity types to include
     */
    public function count(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        array $types = [EntityType::Group, EntityType::GroupAlias],
        ?string $groupType = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): int {
        $total = 0;

        if (in_array(EntityType::Group, $types, true)) {
            $total += $this->buildGroupsQuery($search, $countryId, $cityId, $letter, $groupType, $north, $south, $east, $west)->count(self::GROUPS_TABLE . '.id');
        }

        if (in_array(EntityType::GroupAlias, $types, true)) {
            $total += $this->buildAliasesQuery($search, $countryId, $cityId, $letter, $groupType, $north, $south, $east, $west)->count(self::ALIASES_TABLE . '.id');
        }

        return $total;
    }

    /**
     * @param EntityType[] $types
     * @return Builder[]
     */
    private function buildTypedQueries(
        array $types,
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter,
        ?string $groupType = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): array {
        $queries = [];

        if (in_array(EntityType::Group, $types, true)) {
            $queries[] = $this->buildGroupsQuery($search, $countryId, $cityId, $letter, $groupType, $north, $south, $east, $west);
        }

        if (in_array(EntityType::GroupAlias, $types, true)) {
            $queries[] = $this->buildAliasesQuery($search, $countryId, $cityId, $letter, $groupType, $north, $south, $east, $west);
        }

        return $queries;
    }

    /**
     * @return int[]
     */
    public function findCountryIds(?string $letter = null, ?string $groupType = null): array
    {
        $groupsQuery = $this->db->table(self::GROUPS_TABLE)
            ->distinct()
            ->where(self::GROUPS_TABLE . '.country', '>', 0)
            ->select(self::GROUPS_TABLE . '.country');
        $this->applyLetterFilter($groupsQuery, self::GROUPS_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($groupsQuery, self::GROUPS_TABLE, $groupType);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::GROUPS_TABLE, self::GROUPS_TABLE . '.id', '=', self::ALIASES_TABLE . '.groupId')
            ->where(self::GROUPS_TABLE . '.country', '>', 0)
            ->select(self::GROUPS_TABLE . '.country');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($aliasesQuery, self::GROUPS_TABLE, $groupType);

        $groupsQuery->unionAll($aliasesQuery);

        return $groupsQuery->pluck('country');
    }

    /**
     * @return int[]
     */
    public function findCityIds(?string $letter = null, ?string $groupType = null): array
    {
        $groupsQuery = $this->db->table(self::GROUPS_TABLE)
            ->distinct()
            ->where(self::GROUPS_TABLE . '.city', '>', 0)
            ->select(self::GROUPS_TABLE . '.city');
        $this->applyLetterFilter($groupsQuery, self::GROUPS_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($groupsQuery, self::GROUPS_TABLE, $groupType);

        $aliasesQuery = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(self::GROUPS_TABLE, self::GROUPS_TABLE . '.id', '=', self::ALIASES_TABLE . '.groupId')
            ->where(self::GROUPS_TABLE . '.city', '>', 0)
            ->select(self::GROUPS_TABLE . '.city');
        $this->applyLetterFilter($aliasesQuery, self::ALIASES_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($aliasesQuery, self::GROUPS_TABLE, $groupType);

        $groupsQuery->unionAll($aliasesQuery);

        return $groupsQuery->pluck('city');
    }

    private function buildGroupsQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        ?string $groupType = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): Builder {
        $query = $this->db->table(self::GROUPS_TABLE)
            ->distinct()
            ->select([
                self::GROUPS_TABLE . '.id',
                self::GROUPS_TABLE . '.title',
            ]);

        if ($search !== null && $search !== '') {
            $likeSearch = '%' . $search . '%';
            $query->where(function (Builder $q) use ($likeSearch) {
                $q->where(self::GROUPS_TABLE . '.title', 'like', $likeSearch)
                    ->orWhere(self::GROUPS_TABLE . '.abbreviation', 'like', $likeSearch);
            });
        }

        $this->applyLocationFilters($query, self::GROUPS_TABLE, $countryId, $cityId, $north, $south, $east, $west);

        $this->applyLetterFilter($query, self::GROUPS_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($query, self::GROUPS_TABLE, $groupType);

        return $query;
    }

    private function buildAliasesQuery(
        ?string $search,
        ?int $countryId,
        ?int $cityId,
        ?string $letter = null,
        ?string $groupType = null,
        ?float $north = null,
        ?float $south = null,
        ?float $east = null,
        ?float $west = null,
    ): Builder {
        $grpAlias = 'grp';

        $query = $this->db->table(self::ALIASES_TABLE)
            ->distinct()
            ->leftJoin(
                self::GROUPS_TABLE . ' as ' . $grpAlias,
                $grpAlias . '.id',
                '=',
                self::ALIASES_TABLE . '.groupId'
            )
            ->select([
                self::ALIASES_TABLE . '.id',
                self::ALIASES_TABLE . '.title',
            ]);

        if ($search !== null && $search !== '') {
            $likeSearch = '%' . $search . '%';
            $query->where(self::ALIASES_TABLE . '.title', 'like', $likeSearch);
        }

        $this->applyLocationFilters($query, $grpAlias, $countryId, $cityId, $north, $south, $east, $west);

        $this->applyLetterFilter($query, self::ALIASES_TABLE . '.title', $letter);
        $this->applyGroupTypeFilter($query, $grpAlias, $groupType);

        return $query;
    }

    private function applyGroupTypeFilter(Builder $query, string $groupTableOrAlias, ?string $groupType): void
    {
        if ($groupType === null || $groupType === '' || $groupType === 'all') {
            return;
        }

        $query->where($groupTableOrAlias . '.type', '=', $groupType);
    }

    private function applyLocationFilters(
        Builder $query,
        string $groupTableOrAlias,
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
    ): void {
        if ($countryId !== null) {
            $query->where($groupTableOrAlias . '.country', '=', $countryId);
            return;
        }

        if ($cityId !== null) {
            $query->where($groupTableOrAlias . '.city', '=', $cityId);
            return;
        }

        if (!$this->hasBounds($north, $south, $east, $west)) {
            return;
        }

        $boundsNorth = (float)$north;
        $boundsSouth = (float)$south;
        $boundsEast = (float)$east;
        $boundsWest = (float)$west;

        $query->where(function (Builder $locationQuery) use ($groupTableOrAlias, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest) {
            $locationQuery
                ->whereIn($groupTableOrAlias . '.country', $this->buildLocationBoundsQuery(DatabaseTable::Country, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest))
                ->orWhereIn($groupTableOrAlias . '.city', $this->buildLocationBoundsQuery(DatabaseTable::City, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest));
        });
    }

    private function buildLocationBoundsQuery(
        DatabaseTable $table,
        float $north,
        float $south,
        float $east,
        float $west,
    ): Builder {
        $query = $this->db->table($table->value)
            ->select('id')
            ->where('languageId', '=', $this->getCurrentLanguageId())
            ->where('latitude', '<=', $north)
            ->where('latitude', '>=', $south)
            ->where('longitude', '!=', 0)
            ->where('latitude', '!=', 0);

        $this->applyLongitudeBounds($query, $east, $west);

        return $query;
    }

    private function applyLongitudeBounds(Builder $query, float $east, float $west): void
    {
        if ($west <= $east) {
            $query->where('longitude', '>=', $west)
                ->where('longitude', '<=', $east);
            return;
        }

        $query->where(function (Builder $longitudeQuery) use ($east, $west) {
            $longitudeQuery->where('longitude', '>=', $west)
                ->orWhere('longitude', '<=', $east);
        });
    }

    private function hasBounds(?float $north, ?float $south, ?float $east, ?float $west): bool
    {
        return $north !== null && $south !== null && $east !== null && $west !== null;
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
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
