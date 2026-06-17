<?php

declare(strict_types=1);

namespace ZxArt\Feedback\Exception;

use RuntimeException;

class FeedbackException extends RuntimeException
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
