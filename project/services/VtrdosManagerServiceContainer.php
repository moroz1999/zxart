<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class VtrdosManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return VtrdosManager
     */
    public function makeInstance()
    {
        return new VtrdosManager();
    }

    /**
     * @param VtrdosManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $vtrdosManager = $instance;
        /**
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $vtrdosManager->setProdsService($prodsManager);
        } else {
            $vtrdosManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $vtrdosManager->setAuthorsManager($authorsManager);
        } else {
            $vtrdosManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $vtrdosManager->setGroupsService($groupsService);
        } else {
            $vtrdosManager->setGroupsService($this->registry->getService(GroupsService::class));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $vtrdosManager->setCountriesManager($countriesManager);
        } else {
            $vtrdosManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        return $vtrdosManager;
    }
}