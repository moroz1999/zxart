<?php

declare(strict_types=1);

namespace ZxArt\Shared\Time;

/**
 * The time and waiting, so that what waits can be tested without waiting.
 */
interface Clock
{
    /**
     * Seconds since the Unix epoch, with microseconds.
     */
    public function now(): float;

    public function sleep(float $seconds): void;
}
