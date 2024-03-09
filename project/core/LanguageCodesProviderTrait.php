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

    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getSupportedLanguageNames(): array
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

    public function getSupportedLanguageString(): string
    {
        return implode(', ', $this->getSupportedLanguageNames());
    }

    abstract public function getSupportedLanguageCodes();
}