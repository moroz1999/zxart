<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\ThemeValue;

final class DefaultUserPreferencesProvider
{
    /**
     * @return array<string, string>
     */
    public function getDefaults(): array
    {
        return [
            PreferenceCode::THEME->value => ThemeValue::LIGHT->value,
        ];
    }

    public function getDefault(PreferenceCode $code): ?string
    {
        return $this->getDefaults()[$code->value] ?? null;
    }
}
