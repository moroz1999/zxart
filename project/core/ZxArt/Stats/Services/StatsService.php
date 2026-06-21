<?php

declare(strict_types=1);

namespace ZxArt\Stats\Services;

use App\Logging\EventsLog;
use ConfigManager;
use structureManager;
use userElement;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Stats\Dto\StatsCategorySectionDto;
use ZxArt\Stats\Dto\StatsDailySeriesDto;
use ZxArt\Stats\Dto\StatsDistributionDto;
use ZxArt\Stats\Dto\StatsOverviewDto;
use ZxArt\Stats\Dto\StatsTopUserDto;
use ZxArt\Stats\Dto\StatsUsersSectionDto;
use ZxArt\Stats\Dto\StatsYearSeriesDto;
use ZxArt\Stats\Repositories\StatsRepository;

readonly class StatsService
{
    private const string OTHER_CLASS = 'other';
    private const int DAILY_DAYS = 30;
    private const int DISTRIBUTION_LIMIT = 6;
    private const array BADGE_PRIORITY = ['vip', 'volunteer', 'supporter'];

    public function __construct(
        private StatsRepository $repository,
        private EventsLog $eventsLog,
        private structureManager $structureManager,
        private ConfigManager $configManager,
    ) {
    }

    public function getOverview(): StatsOverviewDto
    {
        $authors = $this->repository->countRowsForLanguage(DatabaseTable::Author);
        $groups = $this->repository->countRows(DatabaseTable::Group);

        return new StatsOverviewDto(
            prods: $this->repository->countRows(DatabaseTable::ZxProd),
            releases: $this->repository->countRows(DatabaseTable::ZxRelease),
            authors: $authors,
            authorsWithAliases: $authors + $this->repository->countRows(DatabaseTable::AuthorAlias),
            groups: $groups,
            groupsWithAliases: $groups + $this->repository->countRows(DatabaseTable::GroupAlias),
            music: $this->repository->countRows(DatabaseTable::ZxMusic),
            pictures: $this->repository->countRows(DatabaseTable::ZxPicture),
        );
    }

    public function getSoftSection(): StatsCategorySectionDto
    {
        [$years, $series] = $this->buildYearSeries(DatabaseTable::ZxProd);
        $distributions = [
            $this->buildDistribution('stats.dist.prod_category', $this->repository->prodCategoryDistribution(), $years),
        ];
        $daily = $this->buildDaily('stats.daily.uploads', ['addZxProd']);
        $top = $this->topEventUsers(['addZxProd'], 10);

        return new StatsCategorySectionDto(
            totalWorks: array_sum($series->all),
            peakYear: $this->peakYear($years, $series->all),
            dailyTotal: array_sum($daily->data),
            topUnitKey: 'stats.unit.prods',
            series: $series,
            distributions: $distributions,
            daily: $daily,
            top: $top,
        );
    }

    public function getMusicSection(): StatsCategorySectionDto
    {
        [$years, $series] = $this->buildYearSeries(DatabaseTable::ZxMusic);
        $distributions = [
            $this->buildDistribution(
                'stats.dist.music_format',
                $this->repository->distributionByColumn(DatabaseTable::ZxMusic, 'type'),
                $years,
            ),
        ];
        $daily = $this->buildDaily('stats.daily.plays', ['play']);
        $top = $this->topEventUsers(['addZxMusic'], 10);

        return new StatsCategorySectionDto(
            totalWorks: array_sum($series->all),
            peakYear: $this->peakYear($years, $series->all),
            dailyTotal: array_sum($daily->data),
            topUnitKey: 'stats.unit.music',
            series: $series,
            distributions: $distributions,
            daily: $daily,
            top: $top,
        );
    }

    public function getGfxSection(): StatsCategorySectionDto
    {
        [$years, $series] = $this->buildYearSeries(DatabaseTable::ZxPicture);
        $distributions = [
            $this->buildDistribution(
                'stats.dist.gfx_type',
                $this->repository->distributionByColumn(DatabaseTable::ZxPicture, 'type'),
                $years,
            ),
        ];
        $daily = $this->buildDaily('stats.daily.views', ['view']);
        $top = $this->topEventUsers(['addZxPicture'], 10);

        return new StatsCategorySectionDto(
            totalWorks: array_sum($series->all),
            peakYear: $this->peakYear($years, $series->all),
            dailyTotal: array_sum($daily->data),
            topUnitKey: 'stats.unit.pics',
            series: $series,
            distributions: $distributions,
            daily: $daily,
            top: $top,
        );
    }

    public function getUsersSection(): StatsUsersSectionDto
    {
        return new StatsUsersSectionDto(
            voters: $this->topVoters(20),
            comments: $this->topEventUsers(['comment'], 20),
            tags: $this->topEventUsers(['tagAdded'], 20),
        );
    }

    /**
     * @return array{0: int[], 1: StatsYearSeriesDto}
     */
    private function buildYearSeries(DatabaseTable $table): array
    {
        $all = $this->repository->countByYear($table);
        $rated = $this->repository->countRatedByYear($table, $this->averageVote());
        $average = $this->repository->averageVoteByYear($table);

        $years = array_keys($all);
        sort($years);

        $allValues = [];
        $ratedValues = [];
        $avgValues = [];
        foreach ($years as $year) {
            $allValues[] = $all[$year];
            $ratedValues[] = $rated[$year] ?? 0;
            $avgValues[] = $average[$year] ?? 0.0;
        }

        return [$years, new StatsYearSeriesDto($years, $allValues, $ratedValues, $avgValues)];
    }

    /**
     * @param array<int, array<string, int>> $perYear
     * @param int[] $years
     */
    private function buildDistribution(string $titleKey, array $perYear, array $years): StatsDistributionDto
    {
        $totals = [];
        foreach ($perYear as $classes) {
            foreach ($classes as $label => $count) {
                $totals[$label] = ($totals[$label] ?? 0) + $count;
            }
        }
        arsort($totals);

        $topLabels = array_slice(array_keys($totals), 0, self::DISTRIBUTION_LIMIT);
        $hasOther = count($totals) > count($topLabels);

        $classes = $topLabels;
        if ($hasOther) {
            $classes[] = self::OTHER_CLASS;
        }

        $rows = [];
        foreach ($years as $year) {
            $yearClasses = $perYear[$year] ?? [];
            $row = [];
            foreach ($topLabels as $label) {
                $row[] = $yearClasses[$label] ?? 0;
            }
            if ($hasOther) {
                $other = 0;
                foreach ($yearClasses as $label => $count) {
                    if (!in_array($label, $topLabels, true)) {
                        $other += $count;
                    }
                }
                $row[] = $other;
            }
            $rows[] = $row;
        }

        return new StatsDistributionDto($titleKey, $classes, $rows);
    }

    /**
     * @param string[] $types
     */
    private function buildDaily(string $labelKey, array $types): StatsDailySeriesDto
    {
        $counts = $this->eventsLog->countEvents(
            $types,
            null,
            null,
            time() - self::DAILY_DAYS * 86400,
            null,
            'time',
            'asc',
            null,
            'day',
        );

        $dates = [];
        $data = [];
        $now = time();
        for ($offset = self::DAILY_DAYS - 1; $offset >= 0; $offset--) {
            $label = date('d.m.Y', $now - $offset * 86400);
            $dates[] = $label;
            $data[] = (int)($counts[$label] ?? 0);
        }

        return new StatsDailySeriesDto($labelKey, $dates, $data);
    }

    /**
     * @param string[] $types
     *
     * @return StatsTopUserDto[]
     */
    private function topEventUsers(array $types, int $limit): array
    {
        $counts = $this->eventsLog->countEvents($types, null, null, null, null, 'count', 'desc', $limit, 'userId');

        $result = [];
        foreach ($counts as $userId => $count) {
            if ($dto = $this->buildTopUser((int)$userId, (int)$count)) {
                $result[] = $dto;
            }
        }

        return $result;
    }

    /**
     * @return StatsTopUserDto[]
     */
    private function topVoters(int $limit): array
    {
        $result = [];
        foreach ($this->repository->topVoters($limit) as $userId => $count) {
            if ($dto = $this->buildTopUser($userId, $count)) {
                $result[] = $dto;
            }
        }

        return $result;
    }

    private function buildTopUser(int $userId, int $count): ?StatsTopUserDto
    {
        $element = $this->structureManager->getElementById($userId, null, true);
        if (!$element instanceof userElement) {
            return null;
        }

        return new StatsTopUserDto(
            name: html_entity_decode((string)$element->userName, ENT_QUOTES),
            url: $element->getUrl() ?: null,
            badge: $this->resolveBadge($element),
            count: $count,
        );
    }

    private function resolveBadge(userElement $element): ?string
    {
        $badges = $element->getBadgetTypes();
        foreach (self::BADGE_PRIORITY as $badge) {
            if (in_array($badge, $badges, true)) {
                return $badge;
            }
        }

        return null;
    }

    /**
     * @param int[] $years
     * @param int[] $values
     */
    private function peakYear(array $years, array $values): int
    {
        if ($values === []) {
            return 0;
        }

        $maxIndex = array_keys($values, max($values), true)[0];

        return $years[$maxIndex];
    }

    private function averageVote(): float
    {
        return (float)$this->configManager->get('zx.averageVote');
    }
}
