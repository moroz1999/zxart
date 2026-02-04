<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceCodeException;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceValueException;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\ThemeValue;

final class PreferenceValidator
{
    public function validateCode(string $code): PreferenceCode
    {
        $preferenceCode = PreferenceCode::tryFrom($code);
        if ($preferenceCode === null) {
            throw InvalidPreferenceCodeException::fromCode($code);
        }
        return $preferenceCode;
    }

    public function validateValue(PreferenceCode $code, string $value): string
    {
        return match ($code) {
            PreferenceCode::THEME => $this->validateThemeValue($value),
        };
    }

    private function validateThemeValue(string $value): string
    {
        $themeValue = ThemeValue::tryFrom($value);
        if ($themeValue === null) {
            throw InvalidPreferenceValueException::forPreference(PreferenceCode::THEME->value, $value);
        }
        return $themeValue->value;
    }
}
