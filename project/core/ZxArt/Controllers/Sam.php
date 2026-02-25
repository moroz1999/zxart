<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use Cache;
use ConfigManager;
use controllerApplication;
use renderer;
use user;
use ZxArt\Import\Services\WorldOfSamImport;

class Sam extends controllerApplication
{
    protected $applicationName = 'sam';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return void
     */
    public function initialize()
    {
        ini_set("max_execution_time", 60 * 60 * 8);
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

        $user = $this->getService(user::class);
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService(ConfigManager::class)->get('main.rootMarkerAdmin')]
            );

            $worldOfSamImport = $this->getService(WorldOfSamImport::class);
            $worldOfSamImport->importAll();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}

