<?php

class PicturesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new PicturesManager();
    }

    public function makeInjections($instance)
    {
        $this->injectService($instance, 'db');
        $this->injectService($instance, 'LanguagesManager');
        $this->injectService($instance, 'structureManager');
        return $instance;
    }
}