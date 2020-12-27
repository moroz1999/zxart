<?php

class tagsManagerServiceContainer extends DependencyInjectionServiceContainer
{
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