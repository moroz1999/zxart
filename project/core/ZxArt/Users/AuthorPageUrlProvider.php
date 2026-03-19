<?php

declare(strict_types=1);

namespace ZxArt\Users;

use LanguagesManager;
use structureManager;

readonly class AuthorPageUrlProvider
{
    public function __construct(
        private structureManager $structureManager,
        private LanguagesManager $languagesManager,
    ) {}

    public function getAuthorPageUrl(int $authorId): ?string
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $element = $this->structureManager->getElementById($authorId, $languageId);
        if ($element === null) {
            return null;
        }
        $url = (string)$element->URL;
        return $url !== '' ? $url : null;
    }
}
