<?php

class cssApplication extends controllerApplication
{
    protected $applicationName = 'css';
    public $rendererName = 'CssUniter';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        if ($controller->getParameter('file')) {
            $fileName = $controller->getParameter('file');
        } else {
            $fileName = 'united';
        }
        if ($set = $controller->getParameter('set')) {
            $currentThemeCode = $set;
        } else {
            $currentThemeCode = 'default';
        }

        $requestHeadersManager = $this->getService(requestHeadersManager::class);
        $userAgentEngineType = $requestHeadersManager->getUserAgentEngineType();
        /**
         * @var ResourcesUniterHelper $resourcesUniterHelper
         */
        $resourcesUniterHelper = $this->getService(ResourcesUniterHelper::class);
        $resourcesUniterHelper->setCurrentThemeCode($currentThemeCode);
        $cacheFileName = $resourcesUniterHelper->getCacheCode();
        $cssResources = $resourcesUniterHelper->getCssResources();

        $useDataUri = true;
        $userAgentVersion = $resourcesUniterHelper->getUserAgentCode();
        if ($userAgentVersion == 'ie8' || $userAgentVersion == 'ie7') {
            $useDataUri = false;
        }

        $configManager = $this->getService(ConfigManager::class);
        $vars = [];
        if ($colorsConfig = $configManager->getConfig('colors')) {
            $vars = $colorsConfig->getLinkedData();
            $settingsList = $this->getService(settingsManager::class)->getSettingsList();
            foreach ($vars as $color => &$value) {
                if (!empty($settingsList[$color])) {
                    $value = $settingsList[$color];
                }
            }
        }

        $this->renderer->setVariables($vars);

        // assign data to cssUniterRendererPlugin
        $this->renderer->assign('useDataUri', $useDataUri);
        $this->renderer->assign('fileName', $fileName);
        $this->renderer->assign('resources', $cssResources);
        $this->renderer->assign('cacheFileName', $cacheFileName);
        $this->renderer->assign('userAgentEngineType', $userAgentEngineType);
        $this->renderer->display();
    }

    protected function getFilesList($path)
    {
        $filesList = [];
        $directoryContents = scandir($path);
        foreach ($directoryContents as &$contentFile) {
            $extension = strtolower(pathinfo($contentFile, PATHINFO_EXTENSION));
            if ($extension == 'css') {
                $filesList[] = $path . $contentFile;
            }
        }
        return $filesList;
    }
}

