<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

readonly class PasswordReminderResultRestDto
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }
}
