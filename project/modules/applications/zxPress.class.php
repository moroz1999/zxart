<?php

use App\Users\CurrentUserService;

class zxPressApplication extends controllerApplication
{
    protected $applicationName = 'zxPress';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return void
     */
    public function initialize()
    {
        ini_set("max_execution_time", 60 * 15);
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
             * @var zxPressManager $zxPressManager
             */
            $zxPressManager = $this->getService(ZxPressManager::class);
//            $zxPressManager->importAll();
//            $zxPressManager->parseIssuesPage('https://zxpress.ru/issue.php?id=143');
        }
    }
}




