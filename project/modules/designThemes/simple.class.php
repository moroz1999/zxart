<?php

class simpleDesignTheme extends DesignTheme
{
    protected $inheritedThemes = ['project'];

    /**
     * @return void
     */
    public function initialize()
    {
        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/simple/';
    }
}
