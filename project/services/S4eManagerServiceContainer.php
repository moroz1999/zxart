<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class S4eManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return S4eManager
     */
    public function makeInstance()
    {
        return new S4eManager();
    }

    /**
     * @param S4eManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $s4eManager = $instance;
        /**
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $s4eManager->setProdsService($prodsManager);
        } else {
            $s4eManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $s4eManager->setAuthorsManager($authorsManager);
        } else {
            $s4eManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $s4eManager->setGroupsService($groupsService);
        } else {
            $s4eManager->setGroupsService($this->registry->getService(GroupsService::class));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $s4eManager->setCountriesManager($countriesManager);
        } else {
            $s4eManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        return $s4eManager;
    }
}