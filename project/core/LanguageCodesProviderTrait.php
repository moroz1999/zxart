<?php

use ZxArt\Shared\LanguageCodes;

trait LanguageCodesProviderTrait
{
    /**
     * @return list<string>
     */
    public function getLanguageCodes(): array
    {
        return LanguageCodes::ALL;
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
