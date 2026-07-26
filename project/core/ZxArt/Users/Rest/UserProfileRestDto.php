<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

/**
 * The current user's own account. Only the identity is exposed — the password
 * is the single thing an account may change about itself.
 */
readonly class UserProfileRestDto
{
    public function __construct(
        public string $userName,
        public string $email,
    ) {
    }
}
