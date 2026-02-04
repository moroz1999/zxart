<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain;

readonly class UserPreferenceValue
{
    public function __construct(
        public int $userId,
        public int $preferenceId,
        public string $value,
    ) {
    }
}
