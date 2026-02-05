<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use App\Users\CurrentUser;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\ThemeValue;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;

final readonly class CurrentThemeProvider
{
    public function __construct(
        private CurrentUser $user,
        private PreferencesRepository $preferencesRepository,
        private UserPreferenceValuesRepository $valuesRepository,
        private DefaultUserPreferencesProvider $defaultsProvider,
    ) {
    }

    public function getTheme(): ThemeValue
    {
        if (!$this->user->isAuthorized()) {
            return $this->getDefaultTheme();
        }

        $preference = $this->preferencesRepository->findByCode(PreferenceCode::THEME);
        if ($preference === null) {
            return $this->getDefaultTheme();
        }

        $userId = (int)$this->user->id;
        $userValues = $this->valuesRepository->findByUserId($userId);

        foreach ($userValues as $value) {
            if ($value->preferenceId === $preference->id) {
                $theme = ThemeValue::tryFrom($value->value);
                return $theme ?? $this->getDefaultTheme();
            }
        }

        return $this->getDefaultTheme();
    }

    public function getThemeClass(): string
    {
        return $this->getTheme()->value . '-mode';
    }

    private function getDefaultTheme(): ThemeValue
    {
        $default = $this->defaultsProvider->getDefault(PreferenceCode::THEME);
        $theme = ThemeValue::tryFrom($default ?? '');
        return $theme ?? ThemeValue::LIGHT;
    }
}
