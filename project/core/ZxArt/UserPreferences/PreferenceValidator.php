<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceCodeException;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceValueException;
use ZxArt\UserPreferences\Domain\HomepageModuleId;
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
            PreferenceCode::HOMEPAGE_ORDER,
            PreferenceCode::HOMEPAGE_DISABLED => $this->validateModuleIdList($code, $value),
            PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT,
            PreferenceCode::HOMEPAGE_NEW_PICTURES_LIMIT,
            PreferenceCode::HOMEPAGE_NEW_TUNES_LIMIT,
            PreferenceCode::HOMEPAGE_BEST_DEMOS_LIMIT,
            PreferenceCode::HOMEPAGE_BEST_GAMES_LIMIT,
            PreferenceCode::HOMEPAGE_RECENT_PARTIES_LIMIT,
            PreferenceCode::HOMEPAGE_BEST_PICTURES_MONTH_LIMIT,
            PreferenceCode::HOMEPAGE_LATEST_PRODS_LIMIT,
            PreferenceCode::HOMEPAGE_LATEST_RELEASES_LIMIT,
            PreferenceCode::HOMEPAGE_SUPPORT_PRODS_LIMIT,
            PreferenceCode::HOMEPAGE_UNVOTED_PICTURES_LIMIT,
            PreferenceCode::HOMEPAGE_RANDOM_PICTURES_LIMIT,
            PreferenceCode::HOMEPAGE_UNVOTED_TUNES_LIMIT,
            PreferenceCode::HOMEPAGE_RANDOM_TUNES_LIMIT => $this->validateLimit($code, $value),
            PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING,
            PreferenceCode::HOMEPAGE_BEST_DEMOS_MIN_RATING,
            PreferenceCode::HOMEPAGE_BEST_GAMES_MIN_RATING => $this->validateMinRating($code, $value),
            PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR => $this->validateStartYearOffset($code, $value),
            PreferenceCode::RADIO_CRITERIA => $value,
            PreferenceCode::LANGUAGE => $this->validateLanguageCode($value),
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

    private function validateModuleIdList(PreferenceCode $code, string $value): string
    {
        if ($value === '') {
            return '';
        }

        $ids = explode(',', $value);
        foreach ($ids as $id) {
            $trimmed = trim($id);
            if (HomepageModuleId::tryFrom($trimmed) === null) {
                throw InvalidPreferenceValueException::forPreference($code->value, $value);
            }
        }

        return $value;
    }

    private function validateLimit(PreferenceCode $code, string $value): string
    {
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($intValue === false || $intValue < 1 || $intValue > 50) {
            throw InvalidPreferenceValueException::forPreference($code->value, $value);
        }
        return (string)$intValue;
    }

    private function validateMinRating(PreferenceCode $code, string $value): string
    {
        $floatValue = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($floatValue === false || $floatValue < 0.0 || $floatValue > 5.0) {
            throw InvalidPreferenceValueException::forPreference($code->value, $value);
        }
        return (string)$floatValue;
    }

    private function validateStartYearOffset(PreferenceCode $code, string $value): string
    {
        $intValue = filter_var($value, FILTER_VALIDATE_INT);
        if ($intValue === false || $intValue < 0 || $intValue > 10) {
            throw InvalidPreferenceValueException::forPreference($code->value, $value);
        }
        return (string)$intValue;
    }

    private function validateLanguageCode(string $value): string
    {
        // iso6393 codes: 2-4 lowercase letters, optionally followed by hyphen+letters (e.g. 'm-')
        if (!preg_match('/^[a-z]{2,4}(-[a-z]+)?$/', $value)) {
            throw InvalidPreferenceValueException::forPreference(PreferenceCode::LANGUAGE->value, $value);
        }
        return $value;
    }
}
