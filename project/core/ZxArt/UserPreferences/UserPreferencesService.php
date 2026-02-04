<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use App\Users\CurrentUser;
use ZxArt\UserPreferences\Dto\PreferenceDto;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;

final readonly class UserPreferencesService
{
    public function __construct(
        private CurrentUser $user,
        private PreferencesRepository $preferencesRepository,
        private UserPreferenceValuesRepository $valuesRepository,
        private DefaultUserPreferencesProvider $defaultsProvider,
        private PreferenceValidator $validator,
    ) {
    }

    /**
     * @return PreferenceDto[]
     */
    public function getAllPreferences(): array
    {
        $defaults = $this->defaultsProvider->getDefaults();

        if (!$this->user->isAuthorized()) {
            return $this->buildDtosFromDefaults($defaults);
        }

        $userId = (int)$this->user->id;
        $userValues = $this->valuesRepository->findByUserId($userId);

        $valuesByPreferenceId = [];
        foreach ($userValues as $value) {
            $valuesByPreferenceId[$value->preferenceId] = $value->value;
        }

        $preferences = $this->preferencesRepository->findAll();
        $result = [];

        foreach ($preferences as $preference) {
            $value = $valuesByPreferenceId[$preference->id]
                ?? $defaults[$preference->code->value]
                ?? null;

            if ($value !== null) {
                $result[] = new PreferenceDto(
                    code: $preference->code->value,
                    value: $value,
                );
            }
        }

        foreach ($defaults as $code => $defaultValue) {
            $found = false;
            foreach ($result as $dto) {
                if ($dto->code === $code) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = new PreferenceDto(code: $code, value: $defaultValue);
            }
        }

        return $result;
    }

    /**
     * @return PreferenceDto[]
     */
    public function setPreference(string $code, string $value): array
    {
        $preferenceCode = $this->validator->validateCode($code);
        $validatedValue = $this->validator->validateValue($preferenceCode, $value);

        if (!$this->user->isAuthorized()) {
            return $this->getAllPreferences();
        }

        $preference = $this->preferencesRepository->findByCode($preferenceCode);
        if ($preference === null) {
            return $this->getAllPreferences();
        }

        $userId = (int)$this->user->id;
        $this->valuesRepository->upsert($userId, $preference->id, $validatedValue);

        return $this->getAllPreferences();
    }

    /**
     * @param array<string, string> $defaults
     * @return PreferenceDto[]
     */
    private function buildDtosFromDefaults(array $defaults): array
    {
        $result = [];
        foreach ($defaults as $code => $value) {
            $result[] = new PreferenceDto(code: $code, value: $value);
        }
        return $result;
    }
}
