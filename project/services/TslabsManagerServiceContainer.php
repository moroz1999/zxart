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
         * @var ProdsService $prodsService
         */
        if ($prodsService = $this->getOption(ProdsService::class)) {
            $tslabsManager->setProdsService($prodsService);
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
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $tslabsManager->setGroupsService($groupsService);
        } else {
            $tslabsManager->setGroupsService($this->registry->getService(GroupsService::class));
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