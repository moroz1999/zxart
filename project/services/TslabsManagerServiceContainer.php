<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

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
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $tslabsManager->setProdsService($prodsManager);
        } else {
            $tslabsManager->setProdsService($this->registry->getService(ProdsService::class));
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