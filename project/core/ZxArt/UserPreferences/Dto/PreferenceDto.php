<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Dto;

readonly class PreferenceDto
{
    public function __construct(
        public string $code,
        public string $value,
    ) {
    }
}
