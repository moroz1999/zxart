<?php

class SpeccyMapsManagerServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return SpeccyMapsManager
     */
    public function makeInstance()
    {
        return new SpeccyMapsManager();
    }

    /**
     * @param SpeccyMapsManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $speccyMapsManager = $instance;

        $this->injectService($speccyMapsManager, ProdsService::class);
        $this->injectService($speccyMapsManager, 'db');

        return $speccyMapsManager;
    }
}