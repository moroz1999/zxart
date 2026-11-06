<?php

declare(strict_types=1);

namespace ZxArt\Shared\Time;

use Override;

final readonly class SystemClock implements Clock
{
    #[Override]
    public function now(): float
    {
        return microtime(true);
    }

    #[Override]
    public function sleep(float $seconds): void
    {
        if ($seconds > 0) {
            usleep((int)round($seconds * 1_000_000.0));
        }
    }
}
