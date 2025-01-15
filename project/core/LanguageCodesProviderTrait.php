<?php

trait LanguageCodesProviderTrait
{
    /**
     * @return string[]
     *
     * @psalm-return list{'be', 'bs', 'by', 'ca', 'cs', 'da', 'de', 'el', 'en', 'eo', 'es', 'eu', 'fi', 'fr', 'gl', 'hr', 'hu', 'is', 'it', 'la', 'lt', 'lv', 'm-', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sh', 'sk', 'sl', 'sr', 'sv', 'tr', 'ua'}
     */
    public function getLanguageCodes(): array
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

    public function getSupportedLanguagesMap(): array
    {
        $languages = [];
        /**
         * @var translationsManager $translationsManager
         */
        $translationsManager = $this->getService('translationsManager');
        foreach ($this->getSupportedLanguageCodes() as $code) {
            $languages[$code] = $translationsManager->getTranslationByName('language.item_' . $code);
        }
        return $languages;
    }

    abstract public function getSupportedLanguageCodes();
}