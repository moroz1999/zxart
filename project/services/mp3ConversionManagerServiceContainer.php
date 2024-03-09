<?php

class mp3ConversionManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return mp3ConversionManager
     */
    public function makeInstance()
    {
        return new mp3ConversionManager();
    }

    public function makeInjections($instance)
    {
        return $instance;
    }
}