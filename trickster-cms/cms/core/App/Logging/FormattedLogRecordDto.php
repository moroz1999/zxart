<?php

namespace App\Logging;

/**
 * DTO for formatted display of log record information.
 */
final class FormattedLogRecordDto
{
    public function __construct(
        public string $requestId,
        public string $ip,
        public string $url,
        public string $userAgent,
        public string $formattedStartTime,
        public string $formattedDuration,
        public bool   $completed,
    )
    {
    }
}
