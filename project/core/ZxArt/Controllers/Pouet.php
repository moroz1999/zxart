<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use Cache;
use controller;
use Monolog\Logger;
use structureManager;
use ZxArt\Import\Services\PouetImport;

class Pouet extends LoggedControllerApplication
{
    protected $applicationName = 'pouet';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly Cache $cache,
        private readonly CurrentUserService $currentUserService,
        private readonly structureManager $adminStructureManager,
        private readonly PouetImport $pouetImport,
    ) {
        parent::__construct($controller, $logger);
    }

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
        $this->cache->enable(false, false, true);
        $this->renderer->endOutputBuffering();
        while (ob_get_level()) {
            ob_end_flush();
        }

        flush();

        $user = $this->currentUserService->getCurrentUser();
        $userId = $user->checkUser('crontab', null, true);
        flush();

        if ($userId) {
            $user->switchUser($userId);
            flush();

            $this->adminStructureManager->setRequestedPath($controller->requestedPath);
            $this->adminStructureManager->setPrivilegeChecking(false);
            flush();

            $this->pouetImport->importAll();
            flush();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
