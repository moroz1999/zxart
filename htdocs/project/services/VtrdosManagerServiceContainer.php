<?php

class VtrdosManagerServiceContainer extends DependencyInjectionServiceContainer
{
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
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $vtrdosManager->setAuthorsManager($authorsManager);
        } else {
            $vtrdosManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
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