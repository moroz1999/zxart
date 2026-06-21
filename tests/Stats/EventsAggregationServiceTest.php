<?php

declare(strict_types=1);

namespace ZxArt\Tests\Stats;

use App\Logging\EventsLog;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ZxArt\Stats\StatsEventAggregation;
use ZxArt\Stats\Services\EventsAggregationService;

class EventsAggregationServiceTest extends TestCase
{
    public function testAggregateCallsEventsLogCorrect(): void
    {
        $eventsLogMock = $this->createMock(EventsLog::class);

        $eventsToAggregate = StatsEventAggregation::cases();

        $todayStart = (new DateTimeImmutable('today'))->getTimestamp();
        $aggregateCalls = [];
        $deleteCalls = [];

        $eventsLogMock->expects($this->exactly(count($eventsToAggregate)))
            ->method('aggregateEvents')
            ->willReturnCallback(
                static function (string $type, int $startTime, string $groupColumn) use (&$aggregateCalls): void {
                    $aggregateCalls[] = [$type, $startTime, $groupColumn];
                }
            );

        $eventsLogMock->expects($this->exactly(count($eventsToAggregate)))
            ->method('deleteEvents')
            ->willReturnCallback(
                static function (string $type, int $startTime) use (&$deleteCalls): void {
                    $deleteCalls[] = [$type, $startTime];
                }
            );

        $service = new EventsAggregationService($eventsLogMock);
        $service->aggregate();

        $expectedAggregateCalls = array_map(
            static fn(StatsEventAggregation $event): array => [$event->value, $todayStart, $event->groupColumn()],
            $eventsToAggregate,
        );
        $expectedDeleteCalls = array_map(
            static fn(StatsEventAggregation $event): array => [$event->value, $todayStart],
            $eventsToAggregate,
        );

        $this->assertSame($expectedAggregateCalls, $aggregateCalls);
        $this->assertSame($expectedDeleteCalls, $deleteCalls);
    }
}
