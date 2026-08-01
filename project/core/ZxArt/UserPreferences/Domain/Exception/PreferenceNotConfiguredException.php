<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain\Exception;

use ZxArt\UserPreferences\Domain\PreferenceCode;

final class PreferenceNotConfiguredException extends UserPreferencesException
{
    public static function forCode(PreferenceCode $code): self
    {
        return new self("Preference is not configured: {$code->value}");
    }
}
