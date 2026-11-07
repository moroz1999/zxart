<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain\Exception;

final class InvalidPreferenceValueException extends UserPreferencesException
{
    public static function forPreference(string $code, ?string $value): self
    {
        $displayValue = $value ?? 'null';
        return new self("Invalid value '{$displayValue}' for preference '{$code}'");
    }
}
