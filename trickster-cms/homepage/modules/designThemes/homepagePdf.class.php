<?php

class homepagePdfDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $this->inheritedThemes = ['homepageDocument'];
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'homepage/css/pdf/';
        $this->templatesFolder = $tricksterPath . 'homepage/templates/pdf/';
    }

    public function getCssResources()
    {
        if (is_null($this->cssResources)) {
            $this->cssResources = [];
            $this->appendCssResourceFromTheme('reset.less', 'default');
            $this->appendCssResourceFromTheme('colors_variables.less', 'homepagePublic');
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

