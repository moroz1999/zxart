<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use RuntimeException;

final class FormCreateException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
