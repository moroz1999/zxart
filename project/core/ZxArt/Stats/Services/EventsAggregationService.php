<?php

declare(strict_types=1);

namespace ZxArt\Stats\Services;

use eventsLog;

class EventsAggregationService
{
    public function __construct(
        private eventsLog $eventsLog
    ) {
    }

    public function aggregate(): void
    {
        $todayStart = strtotime("today");

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

        foreach ($eventsToAggregate as [$type, $groupColumn]) {
            $this->eventsLog->aggregateEvents($type, $todayStart, $groupColumn);
            $this->eventsLog->deleteEvents($type, $todayStart);
        }
    }
}
