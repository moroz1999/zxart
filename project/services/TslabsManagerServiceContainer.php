<?php

class TslabsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return TslabsManager
     */
    public function makeInstance()
    {
        return new TslabsManager();
    }

    /**
     * @param TslabsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $tslabsManager = $instance;
        /**
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $tslabsManager->setProdsManager($prodsManager);
        } else {
            $tslabsManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $tslabsManager->setAuthorsManager($authorsManager);
        } else {
            $tslabsManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $tslabsManager->setGroupsManager($groupsManager);
        } else {
            $tslabsManager->setGroupsManager($this->registry->getService('GroupsManager'));
        }
        /**
         * @var CountriesManager $countriesManager
         */
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $tslabsManager->setCountriesManager($countriesManager);
        } else {
            $tslabsManager->setCountriesManager($this->registry->getService('CountriesManager'));
        }

        return $tslabsManager;
    }
}