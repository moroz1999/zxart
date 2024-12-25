<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class PouetManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return PouetManager
     */
    public function makeInstance()
    {
        return new PouetManager();
    }

    /**
     * @param PouetManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $pouetManager = $instance;
        /**
         * @var ProdsService $prodsManager
         */
        if ($prodsManager = $this->getOption(ProdsService::class)) {
            $pouetManager->setProdsService($prodsManager);
        } else {
            $pouetManager->setProdsService($this->registry->getService(ProdsService::class));
        }
        /**
         * @var AuthorsService $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $pouetManager->setAuthorsManager($authorsManager);
        } else {
            $pouetManager->setAuthorsManager($this->registry->getService(AuthorsService::class));
        }
        /**
         * @var GroupsService $groupsService
         */
        if ($groupsService = $this->getOption(GroupsService::class)) {
            $pouetManager->setGroupsService($groupsService);
        } else {
            $pouetManager->setGroupsService($this->registry->getService(GroupsService::class));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $pouetManager->setCountriesManager($countriesManager);
        } else {
            $pouetManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }
        /**
         * @var \Illuminate\Database\Connection $db
         */
        if ($db = $this->getOption('db')) {
            $pouetManager->setDb($db);
        } else {
            $pouetManager->setDb($this->registry->getService('db'));
        }
        $this->injectService($pouetManager, 'QueueService');


        return $pouetManager;
    }
}