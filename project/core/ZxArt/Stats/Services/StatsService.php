<?php

declare(strict_types=1);

namespace ZxArt\Stats\Services;

use App\Logging\EventsLog;
use ConfigManager;
use LanguagesManager;
use structureManager;
use userElement;
use ZxArt\Hardware\HardwareCatalog;
use ZxArt\Hardware\HardwareGroup;
use zxProdCategoryElement;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Stats\Dto\StatsCategorySectionDto;
use ZxArt\Stats\Dto\StatsDailySeriesDto;
use ZxArt\Stats\Dto\StatsDistributionDto;
use ZxArt\Stats\Dto\StatsOverviewDto;
use ZxArt\Stats\Dto\StatsTopUserDto;
use ZxArt\Stats\Dto\StatsUsersSectionDto;
use ZxArt\Stats\Dto\StatsYearSeriesDto;
use ZxArt\Stats\Repositories\StatsRepository;
use ZxArt\Stats\StatsDistributionColumn;
use ZxArt\Stats\StatsUserBadge;

readonly class StatsService
{
    private const int DAILY_DAYS = 30;
    private const int COUNTRY_LIMIT = 15;

    public function __construct(
        private StatsRepository $repository,
        private EventsLog $eventsLog,
        private structureManager $structureManager,
        private ConfigManager $configManager,
        private LanguagesManager $languagesManager,
        private HardwareCatalog $hardwareCatalog,
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
            $this->buildSoftCategoryDistribution($years),
            $this->buildComputerModelDistribution($years),
            $this->buildDistribution('stats.dist.country', $this->repository->prodCountryDistribution(), $years, self::COUNTRY_LIMIT),
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
                $this->repository->distributionByColumn(DatabaseTable::ZxMusic, StatsDistributionColumn::Type),
                $years,
            ),
            $this->buildDistribution('stats.dist.country', $this->repository->musicCountryDistribution(), $years, self::COUNTRY_LIMIT),
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
                $this->repository->distributionByColumn(DatabaseTable::ZxPicture, StatsDistributionColumn::Type),
                $years,
            ),
            $this->buildDistribution('stats.dist.country', $this->repository->pictureCountryDistribution(), $years, self::COUNTRY_LIMIT),
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

        $years = array_keys($all);
        sort($years);

        $allValues = [];
        $ratedValues = [];
        foreach ($years as $year) {
            $allValues[] = $all[$year];
            $ratedValues[] = $rated[$year] ?? 0;
        }

        return [$years, new StatsYearSeriesDto($years, $allValues, $ratedValues)];
    }

    /**
     * @param array<int, array<string, int>> $perYear
     * @param int[] $years
     * @param int|null $limit keep only the $limit most populous classes, or all when null
     */
    private function buildDistribution(string $titleKey, array $perYear, array $years, ?int $limit = null): StatsDistributionDto
    {
        $totals = [];
        foreach ($perYear as $classes) {
            foreach ($classes as $label => $count) {
                $totals[$label] = ($totals[$label] ?? 0) + $count;
            }
        }
        arsort($totals);

        if ($limit !== null) {
            $totals = array_slice($totals, 0, $limit, true);
        }

        $labels = array_map('strval', array_keys($totals));

        $rows = [];
        foreach ($years as $year) {
            $yearClasses = $perYear[$year] ?? [];
            $row = [];
            foreach ($labels as $label) {
                $row[] = $yearClasses[$label] ?? 0;
            }
            $rows[] = $row;
        }

        return new StatsDistributionDto($titleKey, $labels, $rows);
    }

    /**
     * Prods rolled up to the top-level soft categories (direct children of the soft catalogue), per year.
     *
     * @param int[] $years
     */
    private function buildSoftCategoryDistribution(array $years): StatsDistributionDto
    {
        $titleKey = 'stats.dist.prod_category';
        $topCategories = $this->getTopSoftCategories();
        if ($topCategories === []) {
            return new StatsDistributionDto($titleKey, [], []);
        }

        $topByCategory = [];
        foreach ($topCategories as $topId => $category) {
            $treeIds = [];
            $category->getSubCategoriesTreeIds($treeIds);
            foreach ($treeIds as $treeId) {
                $topByCategory[$treeId] = $topId;
            }
        }

        $perYear = [];
        foreach ($this->repository->prodCategoryYearCounts() as $categoryId => $yearCounts) {
            $topId = $topByCategory[$categoryId] ?? null;
            if ($topId === null) {
                continue;
            }
            foreach ($yearCounts as $year => $count) {
                $perYear[$topId][$year] = ($perYear[$topId][$year] ?? 0) + $count;
            }
        }

        $totals = [];
        foreach ($perYear as $topId => $yearCounts) {
            $totals[$topId] = array_sum($yearCounts);
        }
        arsort($totals);

        $classes = [];
        $rows = array_fill(0, count($years), []);
        foreach (array_keys($totals) as $topId) {
            $classes[] = (string)$topCategories[$topId]->getTitle();
            foreach ($years as $index => $year) {
                $rows[$index][] = $perYear[$topId][$year] ?? 0;
            }
        }

        return new StatsDistributionDto($titleKey, $classes, $rows);
    }

    /**
     * Prods grouped by the computer model required by their releases, per year.
     *
     * @param int[] $years
     */
    private function buildComputerModelDistribution(array $years): StatsDistributionDto
    {
        $computerModels = $this->hardwareCatalog->getGroupItems(HardwareGroup::COMPUTERS);
        $perYear = $this->repository->prodComputerModelDistribution($computerModels);

        return $this->buildDistribution('stats.dist.computer_model', $perYear, $years);
    }

    /**
     * @return array<int, zxProdCategoryElement> categoryId => top-level category element
     */
    private function getTopSoftCategories(): array
    {
        $catalogues = $this->structureManager->getElementsByType(
            'zxProdCategoriesCatalogue',
            $this->languagesManager->getCurrentLanguageId(),
        );
        $catalogue = reset($catalogues);
        if ($catalogue === false) {
            return [];
        }

        $result = [];
        foreach ($catalogue->getCategories() as $category) {
            if ($category instanceof zxProdCategoryElement) {
                $result[$category->getId()] = $category;
            }
        }

        return $result;
    }

    /**
     * @param string[] $types
     */
    private function buildDaily(string $labelKey, array $types): StatsDailySeriesDto
    {
        /** @var array<string, int|string> $counts */
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
        /** @var array<int|string, int|string> $counts */
        $counts = $this->eventsLog->countEvents($types, null, null, null, null, 'count', 'desc', $limit, 'userId');

        $result = [];
        foreach ($counts as $userId => $count) {
            $topUser = $this->buildTopUser((int)$userId, (int)$count);
            if ($topUser !== null) {
                $result[] = $topUser;
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
            $topUser = $this->buildTopUser($userId, $count);
            if ($topUser !== null) {
                $result[] = $topUser;
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
            name: html_entity_decode($element->userName, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            url: $this->getUserUrl($element),
            badge: $this->resolveBadge($element),
            count: $count,
        );
    }

    private function getUserUrl(userElement $element): ?string
    {
        $url = $element->getUrl();

        return is_string($url) && $url !== '' ? $url : null;
    }

    private function resolveBadge(userElement $element): ?string
    {
        $badges = $element->getBadgetTypes();
        foreach ($this->badgePriority() as $badge) {
            if (in_array($badge->value, $badges, true)) {
                return $badge->value;
            }
        }

        return null;
    }

    /**
     * @return StatsUserBadge[]
     */
    private function badgePriority(): array
    {
        return [StatsUserBadge::Vip, StatsUserBadge::Volunteer, StatsUserBadge::Supporter];
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
