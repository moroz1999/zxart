<?php

class projectRssDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    /**
     * @return void
     */
    public function initialize()
    {
        $this->generateInheritedThemesNames('rss');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/rss/';
    }
}