<?php

class publicDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $controller = controller::getInstance();

        $pathsManager = $controller->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->inheritedThemes = ['default'];
        $this->cssPath = $tricksterPath . 'cms/css/public/';
        $this->javascriptPath = $tricksterPath . 'cms/js/public/';
        $this->javascriptUrl =  $controller->baseURL . $pathsManager->getRelativePath('trickster') . 'cms/js/public/';
        $this->javascriptFiles = [];
    }
}