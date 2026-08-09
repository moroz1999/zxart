<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

use ZxArt\Users\PasswordReminderAction;

final readonly class PasswordReminderRequestDto
{
    public function __construct(
        public PasswordReminderAction $action,
        public string $email,
        public string $key = '',
        public string $password = '',
        public string $passwordRepeat = '',
    ) {
    }
}
