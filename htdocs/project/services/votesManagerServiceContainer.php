<?php

class votesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new votesManager();
    }

    public function makeInjections($instance)
    {
        $votesManager = $instance;
        return $votesManager;
    }
}