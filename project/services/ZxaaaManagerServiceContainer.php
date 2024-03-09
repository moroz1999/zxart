<?php

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
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $zxaaaManager->setProdsManager($prodsManager);
        } else {
            $zxaaaManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $zxaaaManager->setAuthorsManager($authorsManager);
        } else {
            $zxaaaManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $zxaaaManager->setGroupsManager($groupsManager);
        } else {
            $zxaaaManager->setGroupsManager($this->registry->getService('GroupsManager'));
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