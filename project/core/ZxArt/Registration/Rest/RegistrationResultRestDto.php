<?php

declare(strict_types=1);

namespace ZxArt\Registration\Rest;

readonly class RegistrationResultRestDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
