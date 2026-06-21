<?php

declare(strict_types=1);

namespace ZxArt\Stats\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

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
     * @return array<int, float> year => average vote of voted works
     */
    public function averageVoteByYear(DatabaseTable $table): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->baseYearQuery($table)
            ->where('votes', '>', 0)
            ->selectRaw('year, AVG(votes) AS average')
            ->groupBy('year')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']] = round((float)$row['average'], 2);
        }

        return $result;
    }

    /**
     * Distribution of a direct text column grouped by year.
     *
     * @return array<int, array<string, int>> year => (classLabel => count)
     */
    public function distributionByColumn(DatabaseTable $table, string $column): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->baseYearQuery($table)
            ->where($column, '!=', '')
            ->selectRaw($column . ' AS label, year, count(*) AS amount')
            ->groupBy('year', $column)
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']][(string)$row['label']] = (int)$row['amount'];
        }

        return $result;
    }

    /**
     * Distribution of prods by their linked category, grouped by year.
     *
     * @return array<int, array<string, int>> year => (categoryTitle => count)
     */
    public function prodCategoryDistribution(): array
    {
        $prodTable = $this->tableName(DatabaseTable::ZxProd);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $categoryTable = 'module_zxprodcategory';

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->db->table($prodTable)
            ->join($linksTable, $linksTable . '.childStructureId', '=', $prodTable . '.id')
            ->join($categoryTable, $categoryTable . '.id', '=', $linksTable . '.parentStructureId')
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->where($categoryTable . '.languageId', '=', $this->getCurrentLanguageId())
            ->where($categoryTable . '.title', '!=', '')
            ->where($prodTable . '.year', '>', 0)
            ->select([$categoryTable . '.title AS label', $prodTable . '.year AS year'])
            ->selectRaw('count(*) AS amount')
            ->groupBy($prodTable . '.year', $categoryTable . '.title')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']][(string)$row['label']] = (int)$row['amount'];
        }

        return $result;
    }

    /**
     * @return array<int, int> userId => votes count (comments excluded)
     */
    public function topVoters(int $limit): array
    {
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->selectRaw('userId, count(id) AS amount')
            ->where('type', '!=', 'comment')
            ->where('userId', '>', 0)
            ->groupBy('userId')
            ->orderByRaw('count(id) desc')
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
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $query->selectRaw('year, count(*) AS amount')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['year']] = (int)$row['amount'];
        }

        return $result;
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
    }
}
