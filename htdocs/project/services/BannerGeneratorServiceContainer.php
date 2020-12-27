<?php

class BannerGeneratorServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new BannerGenerator();
    }

    public function makeInjections($instance)
    {
        $bannerGenerator = $instance;
        if ($structureManager = $this->getOption('structureManager')) {
            $bannerGenerator->setStructureManager($structureManager);
        } else {
            $bannerGenerator->setStructureManager($this->registry->getService('structureManager'));
        }
        if ($apiQueriesManager = $this->getOption('ApiQueriesManager')) {
            $bannerGenerator->setApiQueriesManager($apiQueriesManager);
        } else {
            $bannerGenerator->setApiQueriesManager($this->registry->getService('ApiQueriesManager'));
        }
        if ($languagesManager = $this->getOption('LanguagesManager')) {
            $bannerGenerator->setLanguagesManager($languagesManager);
        } else {
            $bannerGenerator->setLanguagesManager($this->registry->getService('LanguagesManager'));
        }
        if ($translationsManager = $this->getOption('translationsManager')) {
            $bannerGenerator->setTranslationsManager($translationsManager);
        } else {
            $bannerGenerator->setTranslationsManager($this->registry->getService('translationsManager'));
        }
        if ($pathsManager = $this->getOption('PathsManager')) {
            $bannerGenerator->setPathsManager($pathsManager);
        } else {
            $bannerGenerator->setPathsManager($this->registry->getService('PathsManager'));
        }
        if ($configManager = $this->getOption('ConfigManager')) {
            $bannerGenerator->setConfigManager($configManager);
        } else {
            $bannerGenerator->setConfigManager($this->registry->getService('ConfigManager'));
        }
        return $bannerGenerator;
    }
}