<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain\Exception;

final class InvalidPreferenceValueException extends UserPreferencesException
{
    public static function forPreference(string $code, string $value): self
    {
        return new self("Invalid value '{$value}' for preference '{$code}'");
    }
}
