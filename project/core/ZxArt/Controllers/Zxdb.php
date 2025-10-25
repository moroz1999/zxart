<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use Cache;
use ZxArt\Import\Services\ZxdbImport;
use controllerApplication;

class Zxdb extends controllerApplication
{
    protected $applicationName = 'wos';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    /**
     * @return void
     */
    public function initialize()
    {
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
             * @var ZxdbImport $zxdbImport
             */
            $zxdbImport = $this->getService(ZxdbImport::class);
            $zxdbImport->importAll();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}

