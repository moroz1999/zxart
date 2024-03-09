<?php

class votesManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance(): votesManager
    {
        return new votesManager();
    }

    public function makeInjections($instance): votesManager
    {
        return $instance;
    }
}