<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class LoginRequestDto
{
    public function __construct(
        public string $userName,
        public string $password,
        public bool $remember = false,
    ) {
    }
}
