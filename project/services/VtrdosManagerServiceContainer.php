<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;

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
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $vtrdosManager->setProdsManager($prodsManager);
        } else {
            $vtrdosManager->setProdsManager($this->registry->getService('ProdsManager'));
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
         * @var GroupsService $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $vtrdosManager->setGroupsManager($groupsManager);
        } else {
            $vtrdosManager->setGroupsManager($this->registry->getService('GroupsManager'));
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