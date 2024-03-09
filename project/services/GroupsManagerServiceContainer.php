<?php

class GroupsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new GroupsManager();
    }

    /**
     * @param GroupsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        if ($db = $this->getOption('db')) {
            $instance->setDb($db);
        } else {
            $instance->setDb($this->registry->getService('db'));
        }
        if ($structureManager = $this->getOption('structureManager')) {
            $instance->setStructureManager($structureManager);
        } else {
            $instance->setStructureManager($this->registry->getService('structureManager'));
        }
        if ($linksManager = $this->getOption('linksManager')) {
            $instance->setLinksManager($linksManager);
        } else {
            $instance->setLinksManager($this->registry->getService('linksManager'));
        }
        if ($countriesManager = $this->getOption('CountriesManager')) {
            $instance->setCountriesManager($countriesManager);
        } else {
            $instance->setCountriesManager($this->registry->getService('CountriesManager'));
        }
        $this->injectService($instance, 'LanguagesManager');
        $this->injectService($instance, 'ConfigManager');
        $this->injectService($instance, 'privilegesMAnager');
        $this->injectService($instance, 'AuthorsManager');

        return $instance;
    }
}