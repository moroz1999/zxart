<?php

class ReleasesResolverServiceContainer extends DependencyInjectionServiceContainer
{
    public function makeInstance()
    {
        return new ReleasesResolver();
    }

    /**
     * @param ReleasesResolver $instance
     * @return mixed
     */
    public function makeInjections($instance)
    {
        $this->injectService($instance, 'prodsManager');

        return $instance;
    }
}