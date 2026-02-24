<?php

use App\Users\CurrentUserService;
use ZxArt\Import\Services\VtrdosImport;

declare(strict_types=1);

class vtrdosApplication extends controllerApplication
{
    protected $applicationName = 'vtrdos';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return never
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

            $vtrdosManager = $this->getService(VtrdosImport::class);
            $vtrdosManager->importAll();
        }
    }
}




