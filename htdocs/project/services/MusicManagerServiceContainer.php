<?php

class MusicManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new MusicManager();
    }

    public function makeInjections($instance)
    {
        $musicManager = $instance;
        $this->injectService($instance, 'db');
        $this->injectService($instance, 'LanguagesManager');

        if ($structureManager = $this->getOption('structureManager')) {
            $musicManager->setStructureManager($structureManager);
        } else {
            $musicManager->setStructureManager($this->registry->getService('structureManager'));
        }
        return $musicManager;
    }
}