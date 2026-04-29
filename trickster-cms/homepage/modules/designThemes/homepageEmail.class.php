<?php

class homepageEmailDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $this->inheritedThemes = ['homepageDocument'];
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'homepage/css/email/';
        $this->templatesFolder = $tricksterPath . 'homepage/templates/email/';
    }
}