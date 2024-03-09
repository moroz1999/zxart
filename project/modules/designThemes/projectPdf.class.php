<?php

class projectPdfDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    public function initialize()
    {
        $this->generateInheritedThemesNames('pdf');
        array_unshift($this->inheritedThemes, 'projectDocument');
        $controller = controller::getInstance();

        $this->cssPath = $controller->getProjectPath() . 'css/pdf/';
        $this->templatesFolder = $controller->getProjectPath() . 'templates/pdf/';
    }
}