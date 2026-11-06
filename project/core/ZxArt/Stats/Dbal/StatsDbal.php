<?php

declare(strict_types=1);

namespace ZxArt\Stats\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Stats\StatsDistributionColumn;

final readonly class StatsDbal extends AbstractDbal
{
    private const string VOTES_HISTORY_USER_INDEX = 'userId';

    private const int AVERAGE_VOTE_PRECISION = 2;

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
     * @return array<int, float> year => average vote of the works that have been rated
     */
    public function averageVoteByYear(DatabaseTable $table): array
    {
        $query = $this->baseYearQuery($table)
            ->where('votes', '>', 0)
            ->select(['year'])
            ->selectRaw('AVG(votes) AS amount');

        /** @var array<int, array{year: int|string, amount: float|string}> $rows */
        $rows = $this->groupBy($query, 'year')
            ->orderBy('year')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']] = round((float)$row['amount'], self::AVERAGE_VOTE_PRECISION);
        }

        return $result;
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

        // Hardware lives on both sides now, so a production counts if the code is
        // on the production itself or on one of its releases. The two sources are
        // read separately and merged on the production id — summing their counts
        // would double-count a production carrying the same code in both places.
        $seen = [];
        foreach ([$this->prodOwnHardwarePairs($computerModels), $this->releaseHardwarePairs($computerModels)] as $pairs) {
            foreach ($pairs as $pair) {
                $seen[(int)$pair['year']][(string)$pair['label']][(int)$pair['prodId']] = true;
            }
        }

        $rows = [];
        foreach ($seen as $year => $byLabel) {
            foreach ($byLabel as $label => $prodIds) {
                $rows[] = ['label' => $label, 'year' => $year, 'amount' => count($prodIds)];
            }
        }

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
        $authorAliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $groupTable = $this->tableName(DatabaseTable::Group);
        $groupAliasTable = $this->tableName(DatabaseTable::GroupAlias);
        $countryTable = $this->tableName(DatabaseTable::Country);

        $authors = $this->db->table($authorshipTable)
            ->join($authorTable, $authorTable . '.id', '=', $authorshipTable . '.authorId')
            ->join($prodTable, $prodTable . '.id', '=', $authorshipTable . '.elementId')
            ->join($countryTable, $countryTable . '.id', '=', $authorTable . '.country')
            ->where($authorshipTable . '.type', '=', EntityType::Prod->value)
            ->where($authorTable . '.languageId', '=', $languageId)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($authorTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        $authorAliases = $this->db->table($authorshipTable)
            ->join($authorAliasTable, $authorAliasTable . '.id', '=', $authorshipTable . '.authorId')
            ->join($authorTable, $authorTable . '.id', '=', $authorAliasTable . '.authorId')
            ->join($prodTable, $prodTable . '.id', '=', $authorshipTable . '.elementId')
            ->join($countryTable, $countryTable . '.id', '=', $authorTable . '.country')
            ->where($authorshipTable . '.type', '=', EntityType::Prod->value)
            ->where($authorTable . '.languageId', '=', $languageId)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($authorTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        $groups = $this->db->table($linksTable)
            ->join($groupTable, $groupTable . '.id', '=', $linksTable . '.parentStructureId')
            ->join($prodTable, $prodTable . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, $countryTable . '.id', '=', $groupTable . '.country')
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_GROUPS->value)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($groupTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        $groupAliases = $this->db->table($linksTable)
            ->join($groupAliasTable, $groupAliasTable . '.id', '=', $linksTable . '.parentStructureId')
            ->join($groupTable, $groupTable . '.id', '=', $groupAliasTable . '.groupId')
            ->join($prodTable, $prodTable . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, $countryTable . '.id', '=', $groupTable . '.country')
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_GROUPS->value)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($groupTable . '.country', '>', 0)
            ->where($prodTable . '.year', '>', 0)
            ->select([$prodTable . '.id AS workId', $prodTable . '.year AS year', $countryTable . '.title AS label']);

        // union() de-duplicates identical work, year, and country rows across every kind of credit.
        /** @var array<int, array{workId: int|string, year: int|string, label: string}> $rows */
        $rows = $authors->union($authorAliases)->union($groups)->union($groupAliases)->get();

        return $this->countryRowsToResult($rows);
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
        $authorAliasTable = $this->tableName(DatabaseTable::AuthorAlias);
        $countryTable = $this->tableName(DatabaseTable::Country);
        $workTableName = $this->tableName($workTable);

        $authors = $this->db->table($linksTable)
            ->join($authorTable, $authorTable . '.id', '=', $linksTable . '.parentStructureId')
            ->join($workTableName, $workTableName . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, $countryTable . '.id', '=', $authorTable . '.country')
            ->where($linksTable . '.type', '=', $authorLink->value)
            ->where($authorTable . '.languageId', '=', $languageId)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($authorTable . '.country', '>', 0)
            ->where($workTableName . '.year', '>', 0)
            ->select([$workTableName . '.id AS workId', $workTableName . '.year AS year', $countryTable . '.title AS label']);

        $authorAliases = $this->db->table($linksTable)
            ->join($authorAliasTable, $authorAliasTable . '.id', '=', $linksTable . '.parentStructureId')
            ->join($authorTable, $authorTable . '.id', '=', $authorAliasTable . '.authorId')
            ->join($workTableName, $workTableName . '.id', '=', $linksTable . '.childStructureId')
            ->join($countryTable, $countryTable . '.id', '=', $authorTable . '.country')
            ->where($linksTable . '.type', '=', $authorLink->value)
            ->where($authorTable . '.languageId', '=', $languageId)
            ->where($countryTable . '.languageId', '=', $languageId)
            ->where($authorTable . '.country', '>', 0)
            ->where($workTableName . '.year', '>', 0)
            ->select([$workTableName . '.id AS workId', $workTableName . '.year AS year', $countryTable . '.title AS label']);

        /** @var array<int, array{workId: int|string, year: int|string, label: string}> $rows */
        $rows = $authors->union($authorAliases)->get();

        return $this->countryRowsToResult($rows);
    }

    /**
     * @return array<int, int> userId => votes count (comments excluded)
     */
    public function topVoters(int $limit): array
    {
        $votesHistoryTable = $this->db->getTablePrefix() . $this->tableName(DatabaseTable::VotesHistory);
        $query = $this->db->table(
            $this->db->raw($votesHistoryTable . ' FORCE INDEX (' . self::VOTES_HISTORY_USER_INDEX . ')'),
        )
            ->select(['userId'])
            ->selectRaw('COUNT(*) AS amount')
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
     * Productions carrying the code themselves.
     *
     * @param string[] $computerModels
     * @return list<array{prodId: int|string, label: string, year: int|string}>
     */
    private function prodOwnHardwarePairs(array $computerModels): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $prodHardwareTable = $this->tableName(DatabaseTable::ZxProdHardware);
        $catalogTable = $this->tableName(DatabaseTable::Hardware);

        return $this->db->table($prodTable)
            ->join($prodHardwareTable, $prodHardwareTable . '.elementId', '=', $prodTable . '.id')
            ->join($catalogTable, $catalogTable . '.id', '=', $prodHardwareTable . '.hardwareId')
            ->where($prodTable . '.year', '>', 0)
            ->whereIn($catalogTable . '.code', $computerModels)
            ->distinct()
            ->get([$prodTable . '.id AS prodId', $catalogTable . '.code AS label', $prodTable . '.year AS year']);
    }

    /**
     * Productions reaching the code through one of their releases.
     *
     * @param string[] $computerModels
     * @return list<array{prodId: int|string, label: string, year: int|string}>
     */
    private function releaseHardwarePairs(array $computerModels): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $releaseHardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $catalogTable = $this->tableName(DatabaseTable::Hardware);

        return $this->db->table($prodTable)
            ->join($linksTable, $linksTable . '.parentStructureId', '=', $prodTable . '.id')
            ->join($releaseHardwareTable, $releaseHardwareTable . '.elementId', '=', $linksTable . '.childStructureId')
            ->join($catalogTable, $catalogTable . '.id', '=', $releaseHardwareTable . '.hardwareId')
            ->where($linksTable . '.type', '=', LinkTypes::STRUCTURE->value)
            ->where($prodTable . '.year', '>', 0)
            ->whereIn($catalogTable . '.code', $computerModels)
            ->distinct()
            ->get([$prodTable . '.id AS prodId', $catalogTable . '.code AS label', $prodTable . '.year AS year']);
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

    /**
     * @param array<int, array{workId: int|string, year: int|string, label: string}> $rows
     *
     * @return array<int, array<string, int>>
     */
    private function countryRowsToResult(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $year = (int)$row['year'];
            $label = $row['label'];
            $result[$year][$label] = ($result[$year][$label] ?? 0) + 1;
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
