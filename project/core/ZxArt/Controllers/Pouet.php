<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use App\Users\CurrentUserService;
use Cache;
use renderer;
use ZxArt\Import\Services\PouetImport;

class Pouet extends LoggedControllerApplication
{
    protected $applicationName = 'pouet';
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

        $user = $this->getService(CurrentUserService::class)->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->getService('adminStructureManager');

            $pouetImport = $this->getService(PouetImport::class);
            $pouetImport->importAll();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
