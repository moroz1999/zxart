<?php

trait JsTranslationsTrait
{
    protected function loadJsTranslations()
    {
        $translationsForJs = [];
        $translationManager = $this->getService(translationsManager::class);
        $config = $this->getService(ConfigManager::class)->getConfig('javascriptTranslations');
        $jsTranslations = $config->getLinkedData();
        $jsTranslations = array_keys(array_filter($jsTranslations));
        foreach ($jsTranslations as $translationName) {
            $translationsForJs[$translationName] = $translationManager->getTranslationByName($translationName);
        }
        return $translationsForJs;
    }
}