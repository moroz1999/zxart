<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use App\Users\CurrentUserService;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\ThemeValue;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;

final readonly class CurrentThemeProvider
{
    public function __construct(
        private CurrentUserService $currentUserService,
        private PreferencesRepository $preferencesRepository,
        private UserPreferenceValuesRepository $valuesRepository,
        private DefaultUserPreferencesProvider $defaultsProvider,
    ) {
    }

    public function getTheme(): ThemeValue
    {
        $user = $this->currentUserService->getCurrentUser();
        $isAuthorized = $user->isAuthorized();
        if ($isAuthorized === false) {
            return $this->getDefaultTheme();
        }

        $preference = $this->preferencesRepository->findByCode(PreferenceCode::THEME);
        if ($preference === null) {
            return $this->getDefaultTheme();
        }

        $userId = (int)$user->id;
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
