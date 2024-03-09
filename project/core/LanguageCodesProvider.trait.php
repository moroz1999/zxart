<?php

trait LanguageCodesProviderTrait
{
    public function getLanguageCodes()
    {
        return [
            "be",
            "bs",
            "by",
            "ca",
            "cs",
            "da",
            "de",
            "el",
            "en",
            "eo",
            "es",
            "eu",
            "fi",
            "fr",
            "gl",
            "hr",
            "hu",
            "is",
            "it",
            "la",
            "lt",
            "lv",
            "m-",
            "nl",
            "no",
            "pl",
            "pt",
            "ro",
            "ru",
            "sh",
            "sk",
            "sl",
            "sr",
            "sv",
            "tr",
            "ua",
        ];
    }

    public function getSupportedLanguageNames()
    {
        $names = [];
        /**
         * @var translationsManager $translationsManager
         */
        $translationsManager = $this->getService('translationsManager');
        foreach ($this->getSupportedLanguageCodes() as $code) {
            $names[] = $translationsManager->getTranslationByName('language.item_' . $code);
        }
        return $names;
    }

    public function getSupportedLanguageString()
    {
        return implode(', ', $this->getSupportedLanguageNames());
    }

    abstract public function getSupportedLanguageCodes();
}