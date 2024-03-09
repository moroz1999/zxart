<?php

class ReleasesResolverServiceContainer extends DependencyInjectionServiceContainer
{
    /**
     * @return ReleasesResolver
     */
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
        $this->injectService($instance, 'ProdsManager');

        return $instance;
    }
}