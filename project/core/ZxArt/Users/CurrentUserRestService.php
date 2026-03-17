<?php

declare(strict_types=1);

namespace ZxArt\Users;

use App\Users\CurrentUserService;
use ZxArt\Users\Rest\CurrentUserRestDto;

class CurrentUserRestService
{
    public function __construct(
        private readonly CurrentUserService $currentUserService,
        private readonly RegistrationUrlsProvider $registrationUrlsProvider,
        private readonly PasswordReminderUrlProvider $passwordReminderUrlProvider,
        private readonly PlaylistsUrlProvider $playlistsUrlProvider,
    ) {}

    public function buildDto(): CurrentUserRestDto
    {
        $user = $this->currentUserService->getCurrentUser();
        $userName = $user->userName ?: 'anonymous';
        $id = null;
        $profileUrl = null;
        $playlistsUrl = null;

        if ($userName !== 'anonymous' && $user->id) {
            $id = (int)$user->id;
            $profileUrl = $this->registrationUrlsProvider->getProfileUrl();
            $playlistsUrl = $this->playlistsUrlProvider->getPlaylistsUrl();
        }

        return new CurrentUserRestDto(
            id: $id,
            userName: $userName,
            registrationUrl: $this->registrationUrlsProvider->getRegistrationUrl(),
            passwordReminderUrl: $this->passwordReminderUrlProvider->getPasswordReminderUrl(),
            profileUrl: $profileUrl,
            playlistsUrl: $playlistsUrl,
        );
    }

    public function buildAnonymousDto(): CurrentUserRestDto
    {
        return new CurrentUserRestDto(
            id: null,
            userName: 'anonymous',
            registrationUrl: $this->registrationUrlsProvider->getRegistrationUrl(),
            passwordReminderUrl: $this->passwordReminderUrlProvider->getPasswordReminderUrl(),
        );
    }
}
