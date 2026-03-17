<?php

declare(strict_types=1);

namespace ZxArt\Users;

use LanguagesManager;
use structureManager;

class RegistrationUrlsProvider
{
    public function __construct(
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
    ) {}

    public function getRegistrationUrl(): ?string
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType('registration', $languageId);
        foreach ($elements as $element) {
            if ($element->type === 'registration') {
                return (string)$element->URL;
            }
        }
        return null;
    }

    public function getProfileUrl(): ?string
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType('registration', $languageId);
        foreach ($elements as $element) {
            if ($element->type === 'userdata') {
                $parent = $this->structureManager->getElementsFirstParent($element->id);
                return $parent ? (string)$parent->URL : null;
            }
        }
        return null;
    }
}
