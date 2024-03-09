<?php

class tagsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return tagsManager
     */
    public function makeInstance()
    {
        return new tagsManager();
    }

    public function makeInjections($instance)
    {
        $tagsManager = $instance;
        return $tagsManager;
    }
}