<?php

declare(strict_types=1);

namespace ZxArt\Users;

use LanguagesManager;
use structureManager;

class PlaylistsUrlProvider
{
    public function __construct(
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
    ) {}

    public function getPlaylistsUrl(): ?string
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType('userPlaylists', $languageId);
        if ($elements) {
            $element = reset($elements);
            return (string)$element->URL;
        }
        return null;
    }
}
