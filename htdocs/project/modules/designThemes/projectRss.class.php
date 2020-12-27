<?php

class projectRssDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    public function initialize()
    {
        $this->generateInheritedThemesNames('rss');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/rss/';
    }
}