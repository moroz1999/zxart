<?php

class speccyMapsApplication extends controllerApplication
{
    protected $applicationName = 'speccyMaps';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    public function initialize()
    {
        ini_set("max_execution_time", 60 * 15);
        ignore_user_abort(true);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService('Cache');
        $cache->enable(false, false, true);
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();
        while (ob_get_level()) {
            ob_end_flush();
        }

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
            );

            /**
             * @var RzxManager $speccyMaps
             */
            $speccyMaps = $this->getService('SpeccyMapsManager');
            $speccyMaps->importAll();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}

