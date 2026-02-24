<?php

use App\Users\CurrentUserService;

class dmdApplication extends controllerApplication
{
    protected $applicationName = 'dmd';
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

    /**
     * @return void
     */
    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(false, false, true);
        $renderer = $this->getService(renderer::class);
        $renderer->endOutputBuffering();
        while (ob_get_level()) {
            ob_end_flush();
        }

        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            /**
             * @var DmdManager $dmdManager
             */
            $dmdManager = $this->getService(DmdManager::class);
            $dmdManager->importAll();
        }
    }
}




