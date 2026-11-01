<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Chat;

use RuntimeException;
use Throwable;

final class AssistantException extends RuntimeException
{
    public function __construct(string $message, private readonly int $statusCode, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
