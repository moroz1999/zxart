<?php

declare(strict_types=1);

namespace ZxArt\Geo\Exception;

use Exception;

/**
 * Carries the HTTP status the countries endpoint should answer with, so the
 * controller does not have to translate failure kinds back into codes.
 */
class GeoException extends Exception
{
    public function __construct(string $message, private readonly int $statusCode = 400)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
