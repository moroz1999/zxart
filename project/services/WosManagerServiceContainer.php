<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class WosManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return WosManager
     */
    public function makeInstance()
    {
        return new WosManager();
    }

    public function makeInjections($instance)
    {
        $wosManager = $instance;
        /**
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $wosManager->setProdsService($prodsManager);
        } else {
            $wosManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $wosManager->setAuthorsManager($authorsManager);
        } else {
            $wosManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $wosManager->setGroupsService($groupsService);
        } else {
            $wosManager->setGroupsService($this->registry->getService(GroupsService::class));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $wosManager->setCountriesManager($countriesManager);
        } else {
            $wosManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        $zxdbConfig = $this->registry->getService('ConfigManager')->getConfig('zxdb');
        $wosManager->setZxdbConfig($zxdbConfig);

        return $wosManager;
    }
}