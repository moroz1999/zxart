<?php

namespace App\Logging;

final readonly class LogRequestDto
{
    public function __construct(
        public string $ip,
        public string $url,
        public string $userAgent,
        public float  $startTime,
    )
    {
    }
}