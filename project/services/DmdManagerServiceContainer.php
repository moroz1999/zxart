<?php

class DmdManagerServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new DmdManager();
    }

    /**
     * @param DmdManager $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $this->injectService($instance, 'structureManager');
        $this->injectService($instance, 'ProdsDownloader');
        $this->injectService($instance, 'AuthorsManager');
        $this->injectService($instance, 'PathsManager');

        return $instance;
    }
}