<?php

declare(strict_types=1);

namespace ZxArt\Registration\Dto;

readonly class RegistrationRequestDto
{
    public function __construct(
        public string $userName,
        public string $email,
        public string $password,
        public string $passwordRepeat,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $company = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postIndex = null,
        public ?string $country = null,
        public ?string $phone = null,
        public ?string $website = null,
    ) {
    }
}
