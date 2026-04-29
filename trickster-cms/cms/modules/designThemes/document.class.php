<?php

class documentDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'cms/css/document/';
        $this->templatesFolder = $tricksterPath . 'cms/templates/document/';
    }
}

