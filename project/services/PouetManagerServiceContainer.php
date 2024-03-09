<?php

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
         * @var ProdsManager $prodsManager
         */
        if ($prodsManager = $this->getOption('ProdsManager')) {
            $pouetManager->setProdsManager($prodsManager);
        } else {
            $pouetManager->setProdsManager($this->registry->getService('ProdsManager'));
        }
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $pouetManager->setAuthorsManager($authorsManager);
        } else {
            $pouetManager->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $pouetManager->setGroupsManager($groupsManager);
        } else {
            $pouetManager->setGroupsManager($this->registry->getService('GroupsManager'));
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

        return $pouetManager;
    }
}