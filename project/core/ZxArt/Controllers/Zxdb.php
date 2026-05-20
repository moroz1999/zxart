<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use Cache;
use controller;
use Monolog\Logger;
use structureManager;
use ZxArt\Import\Services\ZxdbImport;

class Zxdb extends LoggedControllerApplication
{
    protected $applicationName = 'wos';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly Cache $cache,
        private readonly CurrentUserService $currentUserService,
        private readonly structureManager $adminStructureManager,
        private readonly ZxdbImport $zxdbImport,
    ) {
        parent::__construct($controller, $logger);
    }

    /**
     * @return void
     */
    public function initialize()
    {
        //requires more memory for parsing CD isos
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 60 * 30);
        ignore_user_abort(true);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $this->cache->enable(false, false, true);
        $this->renderer->endOutputBuffering();
        while (ob_get_level()) {
            ob_end_flush();
        }

        $user = $this->currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->adminStructureManager->setRequestedPath($controller->requestedPath);
            $this->adminStructureManager->setPrivilegeChecking(false);

            $this->zxdbImport->importAll();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
