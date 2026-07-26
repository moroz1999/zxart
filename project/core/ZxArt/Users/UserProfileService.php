<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;
use structureManager;
use userElement;
use ZxArt\Users\Rest\UserProfileRestDto;

/**
 * Self-service account handling for the authenticated user.
 *
 * An account may change exactly one thing about itself: its password. Name and
 * email identify the account and are read-only here; everything else
 * (userGroups, privileges, verification/ban flags) stays admin-only.
 */
readonly class UserProfileService
{
    public function __construct(
        private CurrentUserService $currentUserService,
        private structureManager $structureManager,
    ) {
    }

    public function getProfile(): ?UserProfileRestDto
    {
        $element = $this->getCurrentUserElement();
        if ($element === null) {
            return null;
        }

        return new UserProfileRestDto(
            userName: $element->userName,
            email: $element->email,
        );
    }

    /**
     * Replaces the account password. The current password is required so an
     * unattended session cannot be turned into a permanent takeover.
     *
     * Persisting bumps the element's `dateModified`, which invalidates any
     * outstanding password-reset link.
     */
    public function changePassword(string $currentPassword, string $newPassword, string $newPasswordRepeat): PasswordChangeResult
    {
        $element = $this->getCurrentUserElement();
        if ($element === null) {
            return PasswordChangeResult::Unauthorized;
        }
        if ($newPassword === '' || $newPassword !== $newPasswordRepeat) {
            return PasswordChangeResult::NewPasswordMismatch;
        }
        if (!password_verify($currentPassword, $element->password)) {
            return PasswordChangeResult::WrongCurrentPassword;
        }

        // the `password` data chunk hashes the assigned value itself
        $element->password = $newPassword;
        $element->persistElementData();

        return PasswordChangeResult::Changed;
    }

    private function getCurrentUserElement(): ?userElement
    {
        $user = $this->currentUserService->getCurrentUser();
        if (empty($user->id)) {
            return null;
        }
        // user elements sit under the `users` catalogue, which regular users cannot
        // walk to from the root — they have to be loaded directly
        $element = $this->structureManager->getElementById((int)$user->id, null, true);
        return $element instanceof userElement ? $element : null;
    }
}
