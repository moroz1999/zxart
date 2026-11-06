<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use ZxArt\Shared\Time\Clock;

/**
 * Keeps the requests of a process to itch.io a second apart and waits out a
 * "429 Too Many Requests": itch.io limits how fast a visitor may ask.
 */
final class ItchIoThrottle
{
    private const float INTERVAL_SECONDS = 1.0;
    private const float DEFAULT_RETRY_SECONDS = 5.0;
    private const float MAX_RETRY_SECONDS = 10.0;

    private ?float $lastRequestAt = null;

    public function __construct(
        private readonly Clock $clock,
    ) {
    }

    public function beforeRequest(): void
    {
        if ($this->lastRequestAt !== null) {
            $left = $this->lastRequestAt + self::INTERVAL_SECONDS - $this->clock->now();
            if ($left > 0) {
                $this->clock->sleep($left);
            }
        }
        $this->lastRequestAt = $this->clock->now();
    }

    /**
     * Waits what the `Retry-After` header of the refusal asks, if it asks in
     * seconds, but never long: a run of the assistant has minutes in all.
     */
    public function afterRefusal(string $retryAfter): void
    {
        $seconds = ctype_digit($retryAfter) ? (float)$retryAfter : self::DEFAULT_RETRY_SECONDS;
        $this->clock->sleep(min($seconds, self::MAX_RETRY_SECONDS));
    }
}
