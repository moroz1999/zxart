<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;

readonly class LoginService
{
    public function __construct(
        private CurrentUserService $currentUserService,
    ) {}

    public function login(string $userName, string $password): ?int
    {
        $user = $this->currentUserService->getCurrentUser();
        $userId = $user->checkUser($userName, $password);
        if ($userId === false) {
            return null;
        }
        return (int)$userId;
    }

    public function switchUser(int $userId): void
    {
        $this->currentUserService->getCurrentUser()->switchUser($userId);
    }

    public function remember(string $userName, int $userId): void
    {
        $this->currentUserService->getCurrentUser()->rememberUser($userName, $userId);
    }

    public function forget(): void
    {
        $this->currentUserService->getCurrentUser()->forgetUser();
    }

    public function logout(): void
    {
        $this->currentUserService->getCurrentUser()->logout();
    }
}
