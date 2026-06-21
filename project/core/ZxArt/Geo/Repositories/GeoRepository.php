<?php

declare(strict_types=1);

namespace ZxArt\Geo\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;
use ZxArt\Shared\SortDirection;

final readonly class GeoRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return array<int, array{id: int, title: string, latitude: float, longitude: float}>
     */
    public function findCountries(): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::Country))
            ->select(['id', 'title', 'latitude', 'longitude'])
            ->where('languageId', '=', $this->getCurrentLanguageId())
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0)
            ->orderBy('title')
            ->get();

        return $this->indexLocationRows($rows);
    }

    /**
     * @return array<int, array{id: int, countryId: int, title: string, latitude: float, longitude: float}>
     */
    public function findCities(): array
    {
        $cityTable = $this->tableName(DatabaseTable::City);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        $rows = $this->db->table($cityTable)
            ->select([
                $cityTable . '.id',
                $cityTable . '.title',
                $cityTable . '.latitude',
                $cityTable . '.longitude',
                $linksTable . '.parentStructureId as countryId',
            ])
            ->leftJoin($linksTable, $linksTable . '.childStructureId', '=', $cityTable . '.id')
            ->where($linksTable . '.type', '=', 'structure')
            ->where($cityTable . '.languageId', '=', $this->getCurrentLanguageId())
            ->where($cityTable . '.latitude', '!=', 0)
            ->where($cityTable . '.longitude', '!=', 0)
            ->orderBy($cityTable . '.title')
            ->get();

        $cities = [];
        /** @var array<string, mixed> $row */
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $cities[$id] = [
                'id' => $id,
                'countryId' => (int)$row['countryId'],
                'title' => (string)$row['title'],
                'latitude' => (float)$row['latitude'],
                'longitude' => (float)$row['longitude'],
            ];
        }

        return $cities;
    }

    /**
     * @return array<int, int>
     */
    public function countAuthorsByCountry(): array
    {
        return $this->countByLocation(
            DatabaseTable::Author,
            'country',
            $this->getCurrentLanguageId()
        );
    }

    /**
     * @return array<int, int>
     */
    public function countAuthorsByCity(): array
    {
        return $this->countByLocation(
            DatabaseTable::Author,
            'city',
            $this->getCurrentLanguageId()
        );
    }

    /**
     * @return array<int, int>
     */
    public function countGroupsByCountry(): array
    {
        return $this->countByLocation(DatabaseTable::Group, 'country');
    }

    /**
     * @return array<int, int>
     */
    public function countGroupsByCity(): array
    {
        return $this->countByLocation(DatabaseTable::Group, 'city');
    }

    /**
     * @return array<int, int>
     */
    public function countPartiesByCountry(): array
    {
        return $this->countByLocation(DatabaseTable::Party, 'country');
    }

    /**
     * @return array<int, int>
     */
    public function countPartiesByCity(): array
    {
        return $this->countByLocation(DatabaseTable::Party, 'city');
    }

    public function countParties(
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
        ?string $search,
    ): int
    {
        return $this->buildPartiesQuery($countryId, $cityId, $north, $south, $east, $west, $search)
            ->count($this->tableColumn(DatabaseTable::Party, 'id'));
    }

    /**
     * @return int[]
     */
    public function findPartyIds(
        int $start,
        int $limit,
        string $sortColumn,
        SortDirection $sortDirection,
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
        ?string $search,
    ): array {
        /** @var int[] $ids */
        $ids = $this->buildPartiesQuery($countryId, $cityId, $north, $south, $east, $west, $search)
            ->orderBy($sortColumn, $sortDirection->value)
            ->offset($start)
            ->limit($limit)
            ->pluck($this->tableColumn(DatabaseTable::Party, 'id'));

        return $ids;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array{id: int, title: string, latitude: float, longitude: float}>
     */
    private function indexLocationRows(array $rows): array
    {
        $locations = [];
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $locations[$id] = [
                'id' => $id,
                'title' => (string)$row['title'],
                'latitude' => (float)$row['latitude'],
                'longitude' => (float)$row['longitude'],
            ];
        }

        return $locations;
    }

    /**
     * @return array<int, int>
     */
    private function countByLocation(DatabaseTable $table, string $locationColumn, ?int $languageId = null): array
    {
        $query = $this->db->table($this->tableName($table))
            ->where($locationColumn, '>', 0);

        if ($languageId !== null) {
            $query->where('languageId', '=', $languageId);
        }

        /** @var array<int|string> $locationIds */
        $locationIds = $query->pluck($locationColumn);

        $counts = [];
        foreach ($locationIds as $locationId) {
            $id = (int)$locationId;
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        return $counts;
    }

    private function buildPartiesQuery(
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
        ?string $search,
    ): Builder
    {
        $query = $this->db->table($this->tableName(DatabaseTable::Party))
            ->select([
                $this->tableColumn(DatabaseTable::Party, 'id'),
                $this->tableColumn(DatabaseTable::Party, 'title'),
            ]);

        $this->applyLocationFilters($query, DatabaseTable::Party, $countryId, $cityId, $north, $south, $east, $west);

        if ($search !== null && $search !== '') {
            $query->where($this->tableColumn(DatabaseTable::Party, 'title'), 'like', '%' . $search . '%');
        }

        return $query;
    }

    private function applyLocationFilters(
        Builder $query,
        DatabaseTable $table,
        ?int $countryId,
        ?int $cityId,
        ?float $north,
        ?float $south,
        ?float $east,
        ?float $west,
    ): void {
        if ($countryId !== null) {
            $query->where($this->tableColumn($table, 'country'), '=', $countryId);
            return;
        }

        if ($cityId !== null) {
            $query->where($this->tableColumn($table, 'city'), '=', $cityId);
            return;
        }

        if (!$this->hasBounds($north, $south, $east, $west)) {
            return;
        }

        $boundsNorth = (float)$north;
        $boundsSouth = (float)$south;
        $boundsEast = (float)$east;
        $boundsWest = (float)$west;

        $query->where(function (Builder $locationQuery) use ($table, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest) {
            $locationQuery
                ->whereIn($this->tableColumn($table, 'country'), $this->buildLocationBoundsQuery(DatabaseTable::Country, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest))
                ->orWhereIn($this->tableColumn($table, 'city'), $this->buildLocationBoundsQuery(DatabaseTable::City, $boundsNorth, $boundsSouth, $boundsEast, $boundsWest));
        });
    }

    private function buildLocationBoundsQuery(
        DatabaseTable $table,
        float $north,
        float $south,
        float $east,
        float $west,
    ): Builder {
        $query = $this->db->table($this->tableName($table))
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
}
