<?php

class homepageDocumentDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'homepage/css/document/';
        $this->templatesFolder = $tricksterPath . 'homepage/templates/document/';
    }
}