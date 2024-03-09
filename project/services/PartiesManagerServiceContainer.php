<?php

class PartiesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new PartiesManager();
    }

    /**
     * @param PartiesManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $partiesManager = $instance;
        if ($structureManager = $this->getOption('structureManager')) {
            $partiesManager->setStructureManager($structureManager);
        } else {
            $partiesManager->setStructureManager($this->registry->getService('structureManager'));
        }
        if ($languagesManager = $this->getOption('LanguagesManager')) {
            $partiesManager->setLanguagesManager($languagesManager);
        } else {
            $partiesManager->setLanguagesManager($this->registry->getService('LanguagesManager'));
        }
        if ($db = $this->getOption('db')) {
            $partiesManager->setDb($db);
        } else {
            $partiesManager->setDb($this->registry->getService('db'));
        }
        return $partiesManager;
    }
}