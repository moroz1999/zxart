<?php

use Illuminate\Database\Capsule\Manager;

class fixApplication extends controllerApplication
{
    protected $applicationName = 'fix';
    public $rendererName = 'smarty';
    protected $structureManager;

    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 60);
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $structureManager = $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
            );

        }
    }

    public function getUrlName()
    {
        return '';
    }
}