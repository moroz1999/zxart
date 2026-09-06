<?php

declare(strict_types=1);

namespace ZxArt\Geo\Repositories;

use Illuminate\Database\Connection;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Reads every country and city for the management screen.
 *
 * {@see GeoRepository} answers the public map and therefore keeps only places
 * that carry coordinates and hold somebody; management needs the opposite — the
 * whole list, blank coordinates included, in every language.
 */
final readonly class GeoManageRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
        private LanguagesManager $languagesManager,
    ) {
    }

    /**
     * @return list<array{id: int, languageId: int, title: string, latitude: float, longitude: float}>
     */
    public function getCountryRows(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::Country))
            ->select(['id', 'languageId', 'title', 'latitude', 'longitude'])
            ->get();

        return $this->buildPlaceRows($rows);
    }

    /**
     * @return list<array{id: int, languageId: int, title: string, latitude: float, longitude: float}>
     */
    public function getCityRows(): array
    {
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::City))
            ->select(['id', 'languageId', 'title', 'latitude', 'longitude'])
            ->get();

        return $this->buildPlaceRows($rows);
    }

    /**
     * The country each city hangs under.
     *
     * @return array<int, int> cityId => countryId
     */
    public function getCityCountries(): array
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $cityTable = $this->tableName(DatabaseTable::City);
        $countryTable = $this->tableName(DatabaseTable::Country);

        // both ends are joined to their module tables, so a city's other links
        // (and a link to anything that is not a country) cannot answer here
        /** @var list<array<string, mixed>> $rows */
        $rows = $this->db->table($linksTable)
            ->select([$linksTable . '.parentStructureId', $linksTable . '.childStructureId'])
            ->join($cityTable, $cityTable . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, $countryTable . '.id', '=', $linksTable . '.parentStructureId')
            ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value)
            ->where($cityTable . '.languageId', '=', $this->languagesManager->getCurrentLanguageId())
            ->where($countryTable . '.languageId', '=', $this->languagesManager->getCurrentLanguageId())
            ->get();

        $countries = [];
        foreach ($rows as $row) {
            $countries[(int)$row['childStructureId']] = (int)$row['parentStructureId'];
        }

        return $countries;
    }

    /**
     * How many authors, groups and parties name each place, as a country or as
     * a city. Deletion is refused above zero and the list shows the number.
     *
     * @return array<int, int> placeId => count
     */
    public function getPlaceUsages(): array
    {
        $counts = [];
        // author rows exist once per interface language, so one is counted
        $currentLanguageId = (int)$this->languagesManager->getCurrentLanguageId();
        $sources = [
            [DatabaseTable::Author, $currentLanguageId],
            [DatabaseTable::Group, null],
            [DatabaseTable::Party, null],
        ];

        foreach ($sources as [$table, $languageId]) {
            foreach (['country', 'city'] as $column) {
                foreach ($this->countByColumn($table, $column, $languageId) as $placeId => $amount) {
                    $counts[$placeId] = ($counts[$placeId] ?? 0) + $amount;
                }
            }
        }

        return $counts;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array{id: int, languageId: int, title: string, latitude: float, longitude: float}>
     */
    private function buildPlaceRows(array $rows): array
    {
        $places = [];
        foreach ($rows as $row) {
            $places[] = [
                'id' => (int)$row['id'],
                'languageId' => (int)$row['languageId'],
                'title' => (string)$row['title'],
                'latitude' => (float)$row['latitude'],
                'longitude' => (float)$row['longitude'],
            ];
        }

        return $places;
    }

    /**
     * @return array<int, int>
     */
    private function countByColumn(DatabaseTable $table, string $column, ?int $languageId): array
    {
        $query = $this->db->table($this->tableName($table))
            ->where($column, '>', 0);
        if ($languageId !== null) {
            $query->where('languageId', '=', $languageId);
        }

        /** @var list<int|string> $placeIds */
        $placeIds = $query->pluck($column);

        $counts = [];
        foreach ($placeIds as $placeId) {
            $id = (int)$placeId;
            $counts[$id] = ($counts[$id] ?? 0) + 1;
        }

        return $counts;
    }
}
