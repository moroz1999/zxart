<?php

trait LanguageCodesProviderTrait
{
    /**
     * @return string[]
     *
     * @psalm-return list{'be', 'bs', 'by', 'ca', 'cs', 'da', 'de', 'el', 'en', 'eo', 'es', 'et', 'eu', 'fi', 'fr', 'gl', 'hr', 'hu', 'is', 'it', 'ja', 'la', 'lt', 'lv', 'm-', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sh', 'sk', 'sl', 'sr', 'sv', 'tr', 'ua', 'he'}
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
            "et",
            "eu",
            "fi",
            "fr",
            "gl",
            "hr",
            "hu",
            "is",
            "it",
            "ja",
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
            "he",
        ];
    }

    public function getSupportedLanguagesMap(): array
    {
        $languages = [];
        /**
         * @var translationsManager $translationsManager
         */
        $translationsManager = $this->getService(translationsManager::class);
        foreach ($this->getSupportedLanguageCodes() as $code) {
            $languages[$code] = $translationsManager->getTranslationByName('language.item_' . $code);
        }
        return $languages;
    }

    abstract public function getSupportedLanguageCodes();

}
