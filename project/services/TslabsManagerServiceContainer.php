<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;

class TslabsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return TslabsManager
     */
    public function makeInstance()
    {
        return new TslabsManager();
    }

    /**
     * @param TslabsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $tslabsManager = $instance;
        /**
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $tslabsManager->setProdsManager($prodsManager);
        } else {
            $tslabsManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $tslabsManager->setAuthorsManager($authorsManager);
        } else {
            $tslabsManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $tslabsManager->setGroupsManager($groupsManager);
        } else {
            $tslabsManager->setGroupsManager($this->registry->getService('GroupsManager'));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $tslabsManager->setCountriesManager($countriesManager);
        } else {
            $tslabsManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        return $tslabsManager;
    }
}