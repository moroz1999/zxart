<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Time;

use ZxArt\Shared\Time\Clock;

/**
 * A clock that stands still until slept on: a sleep moves it forward and is
 * recorded.
 */
final class FakeClock implements Clock
{
    /** @var list<float> */
    public array $sleeps = [];

    public function __construct(private float $now = 1000.0)
    {
    }

    public function now(): float
    {
        return $this->now;
    }

    public function sleep(float $seconds): void
    {
        $this->sleeps[] = $seconds;
        $this->now += $seconds;
    }

    public function advance(float $seconds): void
    {
        $this->now += $seconds;
    }
}
