<?php

class projectAdminDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    /**
     * @return void
     */
    public function initialize()
    {
        $this->generateInheritedThemesNames('admin');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/admin/';
        $this->cssPath = $controller->getProjectPath() . 'css/admin/';
        $this->javascriptPath = $controller->getProjectPath() . 'js/admin/';
        $this->javascriptUrl = '/project/js/admin/';
        $this->javascriptFiles = [
        ];
        $this->imagesFolder = 'images/admin/';
        $this->imagesPath = PUBLIC_PATH . $this->imagesFolder;
    }
}