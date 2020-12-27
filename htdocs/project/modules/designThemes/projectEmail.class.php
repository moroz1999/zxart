<?php

class projectEmailDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    public function initialize()
    {
        $controller = controller::getInstance();

        $this->generateInheritedThemesNames('email');
        array_unshift($this->inheritedThemes, 'projectDocument');
        $this->cssPath = $controller->getProjectPath() . 'css/email/';
        $this->templatesFolder = $controller->getProjectPath() . 'templates/email/';
    }
}