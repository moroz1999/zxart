<?php

class ProdsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new ProdsManager();
    }

    /**
     * @param ProdsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $this->injectService($instance, 'db');

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
        /**
         * @var AuthorsManager $authorsManager
         */
        if ($authorsManager = $this->getOption('AuthorsManager')) {
            $instance->setAuthorsManager($authorsManager);
        } else {
            $instance->setAuthorsManager($this->registry->getService('AuthorsManager'));
        }
        /**
         * @var GroupsManager $groupsManager
         */
        if ($groupsManager = $this->getOption('GroupsManager')) {
            $instance->setGroupsManager($groupsManager);
        } else {
            $instance->setGroupsManager($this->registry->getService('GroupsManager'));
        }
        /**
         * @var PartiesManager $partiesManager
         */
        if ($partiesManager = $this->getOption('PartiesManager')) {
            $instance->setPartiesManager($partiesManager);
        } else {
            $instance->setPartiesManager($this->registry->getService('PartiesManager'));
        }
        /**
         * @var ProdsDownloader $prodsDownloader
         */
        if ($prodsDownloader = $this->getOption('ProdsDownloader')) {
            $instance->setProdsDownloader($prodsDownloader);
        } else {
            $instance->setProdsDownloader($this->registry->getService('ProdsDownloader'));
        }
        /**
         * @var ZxParsingManager $zxParsingManager
         */
        if ($zxParsingManager = $this->getOption('ZxParsingManager')) {
            $instance->setZxParsingManager($zxParsingManager);
        } else {
            $instance->setZxParsingManager($this->registry->getService('ZxParsingManager'));
        }
        if ($privilegesManager = $this->getOption('privilegesManager')) {
            $instance->setPrivilegesManager($privilegesManager);
        } else {
            $instance->setPrivilegesManager($this->registry->getService('privilegesManager'));
        }
        $this->injectService($instance, 'LanguagesManager');
        $this->injectService($instance, 'PathsManager');

        return $instance;
    }
}