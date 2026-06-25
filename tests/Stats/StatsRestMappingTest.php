<?php

declare(strict_types=1);

namespace ZxArt\Tests\Stats;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Stats\Dto\StatsCategorySectionDto;
use ZxArt\Stats\Dto\StatsCategorySummaryDto;
use ZxArt\Stats\Dto\StatsDailySeriesDto;
use ZxArt\Stats\Dto\StatsDistributionBlockDto;
use ZxArt\Stats\Dto\StatsDistributionDto;
use ZxArt\Stats\Dto\StatsDistributionsDto;
use ZxArt\Stats\Dto\StatsTopUserDto;
use ZxArt\Stats\Dto\StatsTopUsersDto;
use ZxArt\Stats\Dto\StatsUsersSectionDto;
use ZxArt\Stats\Dto\StatsYearSeriesDto;
use ZxArt\Stats\Rest\StatsCategorySectionRestDto;
use ZxArt\Stats\Rest\StatsCategorySummaryRestDto;
use ZxArt\Stats\Rest\StatsDailySeriesRestDto;
use ZxArt\Stats\Rest\StatsDistributionBlockRestDto;
use ZxArt\Stats\Rest\StatsDistributionRestDto;
use ZxArt\Stats\Rest\StatsDistributionsRestDto;
use ZxArt\Stats\Rest\StatsTopUserRestDto;
use ZxArt\Stats\Rest\StatsTopUsersRestDto;
use ZxArt\Stats\Rest\StatsUsersSectionRestDto;
use ZxArt\Stats\Rest\StatsYearSeriesRestDto;

final class StatsRestMappingTest extends TestCase
{
    public function testCategorySectionMapsNestedRestDtos(): void
    {
        $mapper = new ObjectMapper();

        $source = new StatsCategorySectionDto(
            1,
            1984,
            2,
            'stats.unit.prods',
            new StatsYearSeriesDto([1984], [1], [1]),
            [new StatsDistributionDto('stats.dist.category', ['Arcade'], [[1]])],
            new StatsDailySeriesDto('stats.daily.soft', ['21.06.2026'], [2]),
            [new StatsTopUserDto('User', null, [], 3)],
        );

        $result = $mapper->map($source, StatsCategorySectionRestDto::class);

        self::assertInstanceOf(StatsCategorySectionRestDto::class, $result);
        self::assertInstanceOf(StatsYearSeriesRestDto::class, $result->series);
        self::assertInstanceOf(StatsDistributionRestDto::class, $result->distributions[0]);
        self::assertInstanceOf(StatsDailySeriesRestDto::class, $result->daily);
        self::assertInstanceOf(StatsTopUserRestDto::class, $result->top[0]);
    }

    public function testUsersSectionMapsNestedRestDtos(): void
    {
        $mapper = new ObjectMapper();
        $user = new StatsTopUserDto('User', null, ['vip'], 3);

        $source = new StatsUsersSectionDto([$user], [$user], [$user]);

        $result = $mapper->map($source, StatsUsersSectionRestDto::class);

        self::assertInstanceOf(StatsUsersSectionRestDto::class, $result);
        self::assertInstanceOf(StatsTopUserRestDto::class, $result->voters[0]);
        self::assertInstanceOf(StatsTopUserRestDto::class, $result->comments[0]);
        self::assertInstanceOf(StatsTopUserRestDto::class, $result->tags[0]);
    }

    public function testBlockDtosMapNestedRestDtos(): void
    {
        $mapper = new ObjectMapper();

        $summary = $mapper->map(new StatsCategorySummaryDto(1, 1984, 2), StatsCategorySummaryRestDto::class);
        $distributions = $mapper->map(
            new StatsDistributionsDto([1984], [new StatsDistributionDto('stats.dist.category', ['Arcade'], [[1]])]),
            StatsDistributionsRestDto::class,
        );
        $distributionBlock = $mapper->map(
            new StatsDistributionBlockDto([1984], new StatsDistributionDto('stats.dist.category', ['Arcade'], [[1]])),
            StatsDistributionBlockRestDto::class,
        );
        $top = $mapper->map(
            new StatsTopUsersDto('stats.unit.prods', [new StatsTopUserDto('User', null, [], 3)]),
            StatsTopUsersRestDto::class,
        );

        self::assertInstanceOf(StatsCategorySummaryRestDto::class, $summary);
        self::assertInstanceOf(StatsDistributionsRestDto::class, $distributions);
        self::assertInstanceOf(StatsDistributionRestDto::class, $distributions->distributions[0]);
        self::assertInstanceOf(StatsDistributionBlockRestDto::class, $distributionBlock);
        self::assertInstanceOf(StatsDistributionRestDto::class, $distributionBlock->distribution);
        self::assertInstanceOf(StatsTopUsersRestDto::class, $top);
        self::assertInstanceOf(StatsTopUserRestDto::class, $top->users[0]);
    }
}
