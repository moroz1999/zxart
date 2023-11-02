<?php

class S4eManagerServiceContainer extends DependencyInjectionServiceContainer
{
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
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $s4eManager->setProdsManager($prodsManager);
        } else {
            $s4eManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $s4eManager->setAuthorsManager($authorsManager);
        } else {
            $s4eManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $s4eManager->setGroupsManager($groupsManager);
        } else {
            $s4eManager->setGroupsManager($this->registry->getService('GroupsManager'));
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