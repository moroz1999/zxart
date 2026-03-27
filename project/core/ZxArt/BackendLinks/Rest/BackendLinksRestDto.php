<?php

declare(strict_types=1);

namespace ZxArt\BackendLinks\Rest;

readonly class BackendLinksRestDto
{
    public function __construct(
        public ?string $homeUrl,
        public ?string $commentsUrl,
        public ?string $supportUrl,
        public ?string $searchUrl,
        public ?string $registrationUrl,
        public ?string $passwordReminderUrl,
        public ?string $profileUrl,
        public ?string $playlistsUrl,
        public ?string $prodCatalogueBaseUrl,
        public ?string $graphicsBaseUrl,
        public ?string $musicBaseUrl,
    ) {}
}
