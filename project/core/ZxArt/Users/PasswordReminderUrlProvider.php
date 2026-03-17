<?php

declare(strict_types=1);

namespace ZxArt\Users;

use LanguagesManager;
use structureManager;

class PasswordReminderUrlProvider
{
    public function __construct(
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
    ) {}

    public function getPasswordReminderUrl(): ?string
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType('passwordReminder', $languageId);
        if ($elements) {
            $element = reset($elements);
            return (string)$element->URL;
        }
        return null;
    }
}
