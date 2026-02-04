<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Rest;

readonly class PreferenceRestDto
{
    public function __construct(
        public string $code,
        public string $value,
    ) {
    }
}
