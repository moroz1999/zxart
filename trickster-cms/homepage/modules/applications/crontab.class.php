<?php

class crontabApplication extends controllerApplication
{
    protected $applicationName = 'crontab';
    public $rendererName = 'smarty';

    public function initialize()
    {
        set_time_limit(60 * 60);
        $this->startSession('crontab');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(false, false, false);

        $renderer = $this->getService(renderer::class);
        $renderer->endOutputBuffering();

        $emailDispatcher = $this->getService(EmailDispatcher::class);
        $emailDispatcher->setOneDispatchmentDelay(2);
        $emailDispatcher->setTimeLimit(290);
        $emailDispatcher->dispatchAwaitingList();
        $emailDispatcher->clearOutdatedDispatchmentsData();

        $projectPath = $controller->getProjectPath();
        $basketScriptsPath = $projectPath . 'js/shoppingBasketData/';
        $pathsManager = $this->pathsManager;
        $pathsManager->ensureDirectory($basketScriptsPath);
        $configManager = $this->getService(ConfigManager::class);

        if ($configManager->get('main.smartPostEnabled') && class_exists('smartPostImportManager')) {
            $smartPostImportManager = new smartPostImportManager();
            if ($postAutomates = $smartPostImportManager->getPostAutomates()) {
                file_put_contents($basketScriptsPath . 'smartpost.js', $postAutomates);
            }
        }
        if ($configManager->get('main.dpdEnabled') && class_exists('dpdImportManager')) {
            $dpdImportManager = new dpdImportManager();
            if ($postAutomates = $dpdImportManager->getInfo()) {
                file_put_contents($basketScriptsPath . 'dpd.js', 'window.dpdList = ' . json_encode($postAutomates));
            }
        }
        if ($configManager->get('main.post24Enabled') && class_exists('post24ImportManager')) {
            $post24ImportManager = new post24ImportManager();
            if ($postAutomates = $post24ImportManager->getPostAutomates()) {
                file_put_contents($basketScriptsPath . 'post24.js', 'window.post24List = ' . $postAutomates);
            }
        }
    }
}
