<?php

declare(strict_types=1);

namespace ZxArt\Stats\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
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
     * @return array<int, float> year => average vote of voted works
     */
    public function averageVoteByYear(DatabaseTable $table): array
    {
        $result = [];
        $query = $this->baseYearQuery($table)->where('votes', '>', 0);

        foreach ($this->getYears($query) as $year) {
            /** @var int|float|string|null $average */
            $average = (clone $query)->where('year', '=', $year)->avg('votes');
            $result[$year] = round((float)$average, 2);
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
            ->where($column->value, '!=', '');

        $result = [];
        $years = $this->getYears($query);
        $labels = $this->getColumnValues($query, $column->value);

        foreach ($years as $year) {
            foreach ($labels as $label) {
                $count = (clone $query)
                    ->where('year', '=', $year)
                    ->where($column->value, '=', $label)
                    ->count();

                if ($count > 0) {
                    $result[$year][$label] = $count;
                }
            }
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
        $categoryTable = $this->tableName(DatabaseTable::ZxProdCategory);

        $query = $this->prodCategoryQuery($prodTable, $linksTable, $categoryTable)
            ->select([$categoryTable . '.title AS label', $prodTable . '.year AS year'])
            ->distinct();

        /** @var array<int, array{label: string, year: int|string}> $rows */
        $rows = $query->get();

        $result = [];
        foreach ($rows as $row) {
            $year = (int)$row['year'];
            $label = $row['label'];
            $count = $this->prodCategoryQuery($prodTable, $linksTable, $categoryTable)
                ->where($prodTable . '.year', '=', $year)
                ->where($categoryTable . '.title', '=', $label)
                ->count();

            if ($count > 0) {
                $result[$year][$label] = $count;
            }
        }

        return $result;
    }

    /**
     * @return array<int, int> userId => votes count (comments excluded)
     */
    public function topVoters(int $limit): array
    {
        $query = $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->where('type', '!=', 'comment')
            ->where('userId', '>', 0);

        /** @var array<int, int|string> $userIds */
        $userIds = (clone $query)->select(['userId'])->distinct()->pluck('userId');

        $result = [];
        foreach ($userIds as $userId) {
            $typedUserId = (int)$userId;
            $result[$typedUserId] = (clone $query)->where('userId', '=', $typedUserId)->count();
        }

        arsort($result);

        return array_slice($result, 0, $limit, true);
    }

    private function baseYearQuery(DatabaseTable $table): Builder
    {
        return $this->db->table($this->tableName($table))->where('year', '>', 0);
    }

    private function prodCategoryQuery(string $prodTable, string $linksTable, string $categoryTable): Builder
    {
        return $this->db->table($prodTable)
            ->join($linksTable, $linksTable . '.childStructureId', '=', $prodTable . '.id')
            ->join($categoryTable, $categoryTable . '.id', '=', $linksTable . '.parentStructureId')
            ->where($linksTable . '.type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
            ->where($categoryTable . '.languageId', '=', $this->getCurrentLanguageId())
            ->where($categoryTable . '.title', '!=', '')
            ->where($prodTable . '.year', '>', 0);
    }

    /**
     * @return array<int, int> year => count
     */
    private function yearCounts(Builder $query): array
    {
        $result = [];

        foreach ($this->getYears($query) as $year) {
            $result[$year] = (clone $query)->where('year', '=', $year)->count();
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function getYears(Builder $query): array
    {
        /** @var array<int, int|string> $years */
        $years = (clone $query)->select(['year'])->distinct()->orderBy('year')->pluck('year');

        return array_map(static fn(int|string $year): int => (int)$year, $years);
    }

    /**
     * @return string[]
     */
    private function getColumnValues(Builder $query, string $column): array
    {
        /** @var array<int, string> $values */
        $values = (clone $query)->select([$column])->distinct()->orderBy($column)->pluck($column);

        return $values;
    }

    private function getCurrentLanguageId(): int
    {
        return (int)$this->languagesManager->getCurrentLanguageId();
    }
}
