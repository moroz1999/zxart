<?php

use App\Users\CurrentUserService;
use TslabsManager;

class tslabsApplication extends controllerApplication
{
    protected $applicationName = 'tslabs';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return void
     */
    public function initialize()
    {
        ini_set("max_execution_time", 60);
        ignore_user_abort(true);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    /**
     * @return void
     */
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

        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
            );

            $tslabsManager = $this->getService(TslabsManager::class);
            $tslabsManager->importAll();
        }
    }
}




