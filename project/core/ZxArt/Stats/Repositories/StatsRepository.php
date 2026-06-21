<?php

declare(strict_types=1);

namespace ZxArt\Stats\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;
use ZxArt\Stats\StatsDistributionColumn;

final readonly class StatsRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
        private LanguagesManager $languagesManager,
    ) {
    }

    public function countRows(DatabaseTable $table): int
    {
        return $this->db->table($this->tableName($table))->count();
    }

    public function countRowsForLanguage(DatabaseTable $table): int
    {
        return $this->db->table($this->tableName($table))
            ->where('languageId', '=', $this->getCurrentLanguageId())
            ->count();
    }

    /**
     * @return array<int, int> year => count
     */
    public function countByYear(DatabaseTable $table): array
    {
        return $this->yearCounts($this->baseYearQuery($table));
    }

    /**
     * @return array<int, int> year => count of works rated above the average vote
     */
    public function countRatedByYear(DatabaseTable $table, float $averageVote): array
    {
        return $this->yearCounts($this->baseYearQuery($table)->where('votes', '>', $averageVote));
    }

    /**
     * Distribution of a direct text column grouped by year.
     *
     * @return array<int, array<string, int>> year => (classLabel => count)
     */
    public function distributionByColumn(DatabaseTable $table, StatsDistributionColumn $column): array
    {
        $query = $this->baseYearQuery($table)
            ->where($column->value, '!=', '')
            ->select([$column->value . ' AS label', 'year'])
            ->selectRaw('COUNT(*) AS amount');

        /** @var array<int, array{label: string, year: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, 'year', $column->value)->get();

        return $this->distributionRowsToResult($rows);
    }

    /**
     * Prod counts grouped by their directly linked category id and year.
     *
     * Titles and the top-level rollup are resolved from the structure tree by the caller.
     *
     * @return array<int, array<int, int>> categoryId => (year => count)
     */
    public function prodCategoryYearCounts(): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        $query = $this->db->table($prodTable)
            ->join($linksTable, $linksTable . '.childStructureId', '=', $prodTable . '.id')
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->where($prodTable . '.year', '>', 0)
            ->select([$linksTable . '.parentStructureId AS categoryId', $prodTable . '.year AS year'])
            ->selectRaw('COUNT(*) AS amount');

        /** @var array<int, array{categoryId: int|string, year: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, $linksTable . '.parentStructureId', $prodTable . '.year')->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['categoryId']][(int)$row['year']] = (int)$row['amount'];
        }

        return $result;
    }

    /**
     * Prod counts grouped by the computer model required by their releases and by year.
     *
     * A prod is counted once per (year, computer model), even if several of its releases require the same model.
     *
     * @param string[] $computerModels hardware item values to keep
     *
     * @return array<int, array<string, int>> year => (computerModel => count)
     */
    public function prodComputerModelDistribution(array $computerModels): array
    {
        if ($computerModels === []) {
            return [];
        }

        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $hardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        // selectRaw is not processed by the grammar, so the table prefix must be applied manually here.
        $prodIdColumn = $this->db->getTablePrefix() . $prodTable . '.id';

        $query = $this->db->table($prodTable)
            ->join($linksTable, $linksTable . '.parentStructureId', '=', $prodTable . '.id')
            ->join($releaseTable, $releaseTable . '.id', '=', $linksTable . '.childStructureId')
            ->join($hardwareTable, $hardwareTable . '.elementId', '=', $releaseTable . '.id')
            ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value)
            ->where($prodTable . '.year', '>', 0)
            ->whereIn($hardwareTable . '.value', $computerModels)
            ->select([$hardwareTable . '.value AS label', $prodTable . '.year AS year'])
            ->selectRaw('COUNT(DISTINCT ' . $prodIdColumn . ') AS amount');

        /** @var array<int, array{label: string, year: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, $prodTable . '.year', $hardwareTable . '.value')->get();

        return $this->distributionRowsToResult($rows);
    }

    /**
     * Prod counts grouped by the country of their authors and producer groups, by year.
     *
     * A prod is counted once per (year, country). Countries come from the prod's authors and its groups.
     *
     * @return array<int, array<string, int>> year => (countryTitle => count)
     */
    public function prodCountryDistribution(): array
    {
        $languageId = $this->getCurrentLanguageId();
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $authorshipTable = $this->tableName(DatabaseTable::Authorship);
        $authorTable = $this->tableName(DatabaseTable::Author);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $groupTable = $this->tableName(DatabaseTable::Group);
        $countryTable = $this->tableName(DatabaseTable::Country);

        $authors = $this->db->table($authorshipTable)
            ->join($authorTable, function (JoinClause $join) use ($authorTable, $authorshipTable, $languageId): void {
                $join->on($authorTable . '.id', '=', $authorshipTable . '.authorId')
                    ->where($authorTable . '.languageId', '=', $languageId);
            })
            ->join($prodTable, $prodTable . '.id', '=', $authorshipTable . '.elementId')
            ->join($countryTable, function (JoinClause $join) use ($countryTable, $authorTable, $languageId): void {
                $join->on($countryTable . '.id', '=', $authorTable . '.country')
                    ->where($countryTable . '.languageId', '=', $languageId);
            })
            ->where($authorshipTable . '.type', '=', EntityType::Prod->value)
            ->where($authorTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        $groups = $this->db->table($linksTable)
            ->join($groupTable, function (JoinClause $join) use ($groupTable, $linksTable, $languageId): void {
                $join->on($groupTable . '.id', '=', $linksTable . '.childStructureId')
                    ->where($groupTable . '.languageId', '=', $languageId);
            })
            ->join($prodTable, $prodTable . '.id', '=', $linksTable . '.parentStructureId')
            ->join($countryTable, function (JoinClause $join) use ($countryTable, $groupTable, $languageId): void {
                $join->on($countryTable . '.id', '=', $groupTable . '.country')
                    ->where($countryTable . '.languageId', '=', $languageId);
            })
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_GROUPS->value)
            ->where($groupTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        // union() de-duplicates identical (workId, year, country) rows, so a prod sharing a country
        // through both an author and a group is counted once. Aggregation is then done in PHP.
        /** @var array<int, array{workId: int|string, year: int|string, label: string}> $rows */
        $rows = $authors->union($groups)->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']][(string)$row['label']] = ($result[(int)$row['year']][(string)$row['label']] ?? 0) + 1;
        }

        return $result;
    }

    /**
     * Picture counts grouped by the country of their authors, by year.
     *
     * @return array<int, array<string, int>> year => (countryTitle => count)
     */
    public function pictureCountryDistribution(): array
    {
        return $this->workCountryDistributionByAuthorLink(DatabaseTable::ZxPicture, LinkTypes::AUTHOR_PICTURE);
    }

    /**
     * Music counts grouped by the country of their authors, by year.
     *
     * @return array<int, array<string, int>> year => (countryTitle => count)
     */
    public function musicCountryDistribution(): array
    {
        return $this->workCountryDistributionByAuthorLink(DatabaseTable::ZxMusic, LinkTypes::AUTHOR_MUSIC);
    }

    /**
     * Works counts grouped by the country of their authors (linked via $authorLink), by year.
     *
     * A work is counted once per (year, country).
     *
     * @return array<int, array<string, int>> year => (countryTitle => count)
     */
    private function workCountryDistributionByAuthorLink(DatabaseTable $workTable, LinkTypes $authorLink): array
    {
        $languageId = $this->getCurrentLanguageId();
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $authorTable = $this->tableName(DatabaseTable::Author);
        $countryTable = $this->tableName(DatabaseTable::Country);
        $workTableName = $this->tableName($workTable);
        // selectRaw is not processed by the grammar, so the table prefix must be applied manually here.
        $workIdColumn = $this->db->getTablePrefix() . $workTableName . '.id';

        $query = $this->db->table($linksTable)
            ->join($authorTable, function (JoinClause $join) use ($authorTable, $linksTable, $languageId): void {
                $join->on($authorTable . '.id', '=', $linksTable . '.parentStructureId')
                    ->where($authorTable . '.languageId', '=', $languageId);
            })
            ->join($workTableName, $workTableName . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, function (JoinClause $join) use ($countryTable, $authorTable, $languageId): void {
                $join->on($countryTable . '.id', '=', $authorTable . '.country')
                    ->where($countryTable . '.languageId', '=', $languageId);
            })
            ->where($linksTable . '.type', '=', $authorLink->value)
            ->where($authorTable . '.country', '>', 0)
            ->where($workTableName . '.year', '>', 0)
            ->select([$countryTable . '.title AS label', $workTableName . '.year AS year'])
            ->selectRaw('COUNT(DISTINCT ' . $workIdColumn . ') AS amount');

        /** @var array<int, array{label: string, year: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, $workTableName . '.year', $countryTable . '.title')->get();

        return $this->distributionRowsToResult($rows);
    }

    /**
     * @return array<int, int> userId => votes count (comments excluded)
     */
    public function topVoters(int $limit): array
    {
        $query = $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->select(['userId'])
            ->selectRaw('COUNT(id) AS amount')
            ->where('type', '!=', 'comment')
            ->where('userId', '>', 0);

        /** @var array<int, array{userId: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, 'userId')
            ->orderBy('amount', 'desc')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['userId']] = (int)$row['amount'];
        }

        return $result;
    }

    private function baseYearQuery(DatabaseTable $table): Builder
    {
        return $this->db->table($this->tableName($table))->where('year', '>', 0);
    }

    /**
     * @return array<int, int> year => count
     */
    private function yearCounts(Builder $query): array
    {
        $query = $query
            ->select(['year'])
            ->selectRaw('COUNT(*) AS amount');

        /** @var array<int, array{year: int|string, amount: int|string}> $rows */
        $rows = $this->groupBy($query, 'year')
            ->orderBy('year')
            ->get();

        return $this->yearCountRowsToResult($rows);
    }

    /**
     * @param array<int, array{year: int|string, amount: int|string}> $rows
     *
     * @return array<int, int>
     */
    private function yearCountRowsToResult(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']] = (int)$row['amount'];
        }

        return $result;
    }

    /**
     * @param array<int, array{label: string, year: int|string, amount: int|string}> $rows
     *
     * @return array<int, array<string, int>>
     */
    private function distributionRowsToResult(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']][$row['label']] = (int)$row['amount'];
        }

        return $result;
    }

    private function groupBy(Builder $query, string ...$columns): Builder
    {
        call_user_func_array([$query, 'groupBy'], $columns);

        return $query;
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
    }
}
