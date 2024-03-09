<?php

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
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $wosManager->setProdsManager($prodsManager);
        } else {
            $wosManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $wosManager->setAuthorsManager($authorsManager);
        } else {
            $wosManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $wosManager->setGroupsManager($groupsManager);
        } else {
            $wosManager->setGroupsManager($this->registry->getService('GroupsManager'));
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