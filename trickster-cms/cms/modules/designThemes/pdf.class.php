<?php

class pdfDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $this->inheritedThemes = ['document'];
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'cms/css/pdf/';
        $this->templatesFolder = $tricksterPath . 'cms/templates/pdf/';
    }

    public function getCssResources()
    {
        if (is_null($this->cssResources)) {
            $this->cssResources = [];
            $this->appendCssResourceFromTheme('reset.less', 'public');
            $this->appendCssResourceFromTheme('module.order.less', 'public');
            $this->loadCssResources();
        }
        return $this->cssResources;
    }

    public function getImageUrl($fileName, $recursion = false, $required = true)
    {
        if (!$result = parent::getImageUrl($fileName, $recursion, $required)) {
            $configurationManager = controller::getInstance()->getConfigManager();
            $publicThemeName = $configurationManager->get('main.publicTheme');
            if ($theme = $this->designThemesManager->getTheme($publicThemeName)) {
                return $theme->getImageUrl($fileName, $recursion, $required);
            }
        }
        return $result;
    }
}

