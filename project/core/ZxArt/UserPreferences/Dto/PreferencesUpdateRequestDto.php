<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Dto;

final readonly class PreferencesUpdateRequestDto
{
    /** @param list<PreferenceDto> $preferences */
    public function __construct(public array $preferences)
    {
    }
}
