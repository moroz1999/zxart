<?php

declare(strict_types=1);

namespace ZxArt\Users\Rest;

readonly class CurrentUserRestDto
{
    public function __construct(
        public ?int $id,
        public string $userName,
        public ?string $registrationUrl = null,
        public ?string $passwordReminderUrl = null,
        public ?string $profileUrl = null,
        public ?string $playlistsUrl = null,
        public ?string $authorPageUrl = null,
    ) {
    }
}
