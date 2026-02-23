<?php

use App\Users\CurrentUserService;
use ZxaaaManager;

class aaaApplication extends controllerApplication
{
    protected $applicationName = 'aaa';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return never
     */
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

        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
            );

            $zxaaaManager = $this->getService(ZxaaaManager::class);
            $zxaaaManager->importAll();
        }
    }
}




