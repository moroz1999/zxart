<?php

class javascriptApplication extends controllerApplication
{
    protected $applicationName = 'javascript';
    public $rendererName = 'javascriptUniter';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        if ($fileName = $controller->getParameter('file')) {
            $this->renderer->assign('fileName', $fileName);
        }

        if ($set = $controller->getParameter('set')) {
            $currentThemeCode = $set;
        } else {
            $currentThemeCode = 'default';
        }

        $resourcesUniterHelper = $this->getService(ResourcesUniterHelper::class);
        $resourcesUniterHelper->setCurrentThemeCode($currentThemeCode);
        $cacheFileName = $resourcesUniterHelper->getCacheCode();
        $javascriptResources = $resourcesUniterHelper->getJavascriptResources($controller);

        $this->renderer->assign('cacheFileName', $cacheFileName);
        $this->renderer->assign('resources', $javascriptResources);
        $this->renderer->display();
    }
}