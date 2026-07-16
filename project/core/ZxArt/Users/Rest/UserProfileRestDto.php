<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

/** Editable self-service profile fields for the current user. */
readonly class UserProfileRestDto
{
    public function __construct(
        public string $userName,
        public string $company,
        public string $firstName,
        public string $lastName,
        public string $address,
        public string $city,
        public string $postIndex,
        public string $country,
        public string $email,
        public string $phone,
        public string $website,
        public bool $subscribe,
        public bool $showemail,
    ) {
    }
}
