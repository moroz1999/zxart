<?php

namespace App\Logging;

/**
 * DTO for storing complete log record information.
 */
final readonly class LogRecordDto
{
    public function __construct(
        public string $requestId,
        public string $ip,
        public string $url,
        public string $userAgent,
        public float  $startTime,
        public float  $duration,
        public bool   $completed,
    )
    {
    }
}