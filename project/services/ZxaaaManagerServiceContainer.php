<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class ZxaaaManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return ZxaaaManager
     */
    public function makeInstance()
    {
        return new ZxaaaManager();
    }

    /**
     * @param ZxaaaManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $zxaaaManager = $instance;
        /**
         * @var ProdsService $prodsService
         */
        if ($prodsService = $this->getOption(ProdsService::class)) {
            $zxaaaManager->setProdsService($prodsService);
        } else {
            $zxaaaManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $zxaaaManager->setAuthorsManager($authorsManager);
        } else {
            $zxaaaManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $zxaaaManager->setGroupsService($groupsService);
        } else {
            $zxaaaManager->setGroupsService($this->registry->getService(GroupsService::class));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $zxaaaManager->setCountriesManager($countriesManager);
        } else {
            $zxaaaManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        return $zxaaaManager;
    }
}