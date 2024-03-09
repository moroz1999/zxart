<?php

class redisApplication extends controllerApplication
{
    protected $applicationName = 'redis';
    public $requestParameters = [];
    use ReleaseFormatsProvider;
    use ReleaseFileTypesGatherer;

    /**
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $cache = $this->getService(Cache::class);
        $cache->enable(false, false, true);
        $cache->clear();
        echo 'done';
    }
}

