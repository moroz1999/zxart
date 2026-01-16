<?php

declare(strict_types=1);

namespace ZxArt\Tests\Stats;

use eventsLog;
use PHPUnit\Framework\TestCase;
use ZxArt\Stats\Services\EventsAggregationService;

class EventsAggregationServiceTest extends TestCase
{
    public function testAggregateCallsEventsLogCorrect()
    {
        $eventsLogMock = $this->createMock(eventsLog::class);

        $eventsToAggregate = [
            ['view', 'elementId'],
            ['play', 'elementId'],
            ['vote', 'userId'],
            ['addZxPicture', 'userId'],
            ['addZxMusic', 'userId'],
            ['addZxProd', 'userId'],
            ['comment', 'userId'],
            ['tagAdded', 'userId'],
        ];

        $todayStart = strtotime("today");

        $matcher = $this->exactly(count($eventsToAggregate));
        $eventsLogMock->expects($matcher)
            ->method('aggregateEvents')
            ->with($this->callback(function ($type) use ($eventsToAggregate, $matcher) {
                return $type === $eventsToAggregate[$matcher->numberOfInvocations() - 1][0];
            }), $todayStart, $this->callback(function ($groupColumn) use ($eventsToAggregate, $matcher) {
                return $groupColumn === $eventsToAggregate[$matcher->numberOfInvocations() - 1][1];
            }));

        $deleteMatcher = $this->exactly(count($eventsToAggregate));
        $eventsLogMock->expects($deleteMatcher)
            ->method('deleteEvents')
            ->with($this->callback(function ($type) use ($eventsToAggregate, $deleteMatcher) {
                return $type === $eventsToAggregate[$deleteMatcher->numberOfInvocations() - 1][0];
            }), $todayStart);

        $service = new EventsAggregationService($eventsLogMock);
        $service->aggregate();
    }
}
