<?php

declare(strict_types=1);

namespace ZxArt\Registration\Dto;

readonly class EmailVerificationRequestDto
{
    public function __construct(
        public string $email,
        public string $key,
    ) {
    }
}
