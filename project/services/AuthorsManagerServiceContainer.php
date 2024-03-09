<?php

class AuthorsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new AuthorsManager();
    }

    /**
     * @param AuthorsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $authorsManager = $instance;
        if ($db = $this->getOption('db')) {
            $authorsManager->setDb($db);
        } else {
            $authorsManager->setDb($this->registry->getService('db'));
        }
        if ($structureManager = $this->getOption('structureManager')) {
            $authorsManager->setStructureManager($structureManager);
        } else {
            $authorsManager->setStructureManager($this->registry->getService('structureManager'));
        }
        if ($linksManager = $this->getOption('linksManager')) {
            $authorsManager->setLinksManager($linksManager);
        } else {
            $authorsManager->setLinksManager($this->registry->getService('linksManager'));
        }
        if ($privilegesManager = $this->getOption('privilegesManager')) {
            $authorsManager->setPrivilegesManager($privilegesManager);
        } else {
            $authorsManager->setPrivilegesManager($this->registry->getService('privilegesManager'));
        }
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $authorsManager->setCountriesManager($countriesManager);
        } else {
            $authorsManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }
        if ($languagesManager = $this->getOption('LanguagesManager')) {
            $authorsManager->setLanguagesManager($languagesManager);
        } else {
            $authorsManager->setLanguagesManager($this->registry->getService('LanguagesManager'));
        }
        $this->injectService($instance, 'ConfigManager');
        return $authorsManager;
    }
}