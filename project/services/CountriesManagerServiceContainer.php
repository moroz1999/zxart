<?php

class CountriesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return CountriesManager
     */
    public function makeInstance()
    {
        return new CountriesManager();
    }

    public function makeInjections($instance)
    {
        $countriesManager = $instance;
        if ($db = $this->getOption('db')) {
            $countriesManager->setDb($db);
        } else {
            $countriesManager->setDb($this->registry->getService('db'));
        }

        if ($linksManager = $this->getOption('linksManager')) {
            $countriesManager->setLinksManager($linksManager);
        } else {
            $countriesManager->setLinksManager($this->registry->getService('linksManager'));
        }

        if ($structureManager = $this->getOption('structureManager')) {
            $countriesManager->setStructureManager($structureManager);
        } else {
            $countriesManager->setStructureManager($this->registry->getService('structureManager'));
        }
        return $countriesManager;
    }
}