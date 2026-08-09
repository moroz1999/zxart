<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class PasswordChangeRequestDto
{
    public function __construct(
        public string $currentPassword,
        public string $password,
        public string $passwordRepeat,
    ) {
    }
}
