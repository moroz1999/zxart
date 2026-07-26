<?php

declare(strict_types=1);

namespace ZxArt\Registration\Dto;

readonly class RegistrationRequestDto
{
    /** @param array<string, mixed> $fields */
    public function __construct(
        public string $userName,
        public string $email,
        public string $password,
        public string $passwordRepeat,
        public array $fields,
    ) {
    }
}
