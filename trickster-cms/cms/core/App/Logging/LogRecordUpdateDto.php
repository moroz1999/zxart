<?php

namespace App\Logging;

/**
 * DTO for updating log record information including requestId, startTime, and endTime.
 */
final readonly class LogRecordUpdateDto
{
    public function __construct(
        public string $requestId,
        public float $startTime,
        public float $endTime,
        public bool $completed,
    )
    {
    }
}