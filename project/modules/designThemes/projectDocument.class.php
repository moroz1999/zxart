<?php

class projectDocumentDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    /**
     * @return void
     */
    public function initialize()
    {
        $controller = controller::getInstance();

        $this->generateInheritedThemesNames('document');
        $this->cssPath = $controller->getProjectPath() . 'css/document/';
        $this->templatesFolder = $controller->getProjectPath() . 'templates/document/';
    }
}