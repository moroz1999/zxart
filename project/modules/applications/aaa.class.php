<?php

class aaaApplication extends controllerApplication
{
    protected $applicationName = 'aaa';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    public function initialize()
    {
        exit;
        ini_set("max_execution_time", 60 * 1);
        ignore_user_abort(true);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        exit;
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
             * @var ZxaaaManager $zxaaaManager
             */
            $zxaaaManager = $this->getService('ZxaaaManager');
            $zxaaaManager->importAll();
        }
    }
}

