<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain;

readonly class Preference
{
    public function __construct(
        public int $id,
        public PreferenceCode $code,
        public string $type,
    ) {
    }
}
