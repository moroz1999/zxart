<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use App\Users\CurrentUserService;
use ZxArt\UserPreferences\Dto\PreferenceDto;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;

final readonly class UserPreferencesService
{
    public function __construct(
        private CurrentUserService $currentUserService,
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

        $user = $this->currentUserService->getCurrentUser();
        $isAuthorized = $user->isAuthorized();
        if ($isAuthorized === false) {
            return $this->buildDtosFromDefaults($defaults);
        }

        $userId = (int)$user->id;
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

        $user = $this->currentUserService->getCurrentUser();
        $isAuthorized = $user->isAuthorized();
        if ($isAuthorized === false) {
            return $this->getAllPreferences();
        }

        $preference = $this->preferencesRepository->findByCode($preferenceCode);
        if ($preference === null) {
            return $this->getAllPreferences();
        }

        $userId = (int)$user->id;
        $this->valuesRepository->upsert($userId, $preference->id, $validatedValue);

        return $this->getAllPreferences();
    }

    /**
     * @param array<string, string> $values code => value pairs
     * @return PreferenceDto[]
     */
    public function setPreferences(array $values): array
    {
        foreach ($values as $code => $value) {
            $preferenceCode = $this->validator->validateCode($code);
            $this->validator->validateValue($preferenceCode, $value);
        }

        $user = $this->currentUserService->getCurrentUser();
        $isAuthorized = $user->isAuthorized();
        if ($isAuthorized === false) {
            return $this->getAllPreferences();
        }

        $userId = (int)$user->id;

        foreach ($values as $code => $value) {
            $preferenceCode = $this->validator->validateCode($code);
            $validatedValue = $this->validator->validateValue($preferenceCode, $value);
            $preference = $this->preferencesRepository->findByCode($preferenceCode);
            if ($preference !== null) {
                $this->valuesRepository->upsert($userId, $preference->id, $validatedValue);
            }
        }

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
