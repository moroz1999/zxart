<?php

declare(strict_types=1);

namespace ZxArt\Stats\Services;

use App\Logging\EventsLog;
use DateTimeImmutable;
use ZxArt\Stats\StatsEventAggregation;

final readonly class EventsAggregationService
{
    public function __construct(
        private EventsLog $eventsLog
    ) {
    }

    public function aggregate(): void
    {
        $todayStart = (new DateTimeImmutable('today'))->getTimestamp();

        foreach (StatsEventAggregation::cases() as $event) {
            $this->eventsLog->aggregateEvents($event->value, $todayStart, $event->groupColumn());
            $this->eventsLog->deleteEvents($event->value, $todayStart);
        }
    }
}
