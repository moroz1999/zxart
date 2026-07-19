<?php

class projectDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    /**
     * @return void
     */
    #[Override]
    public function initialize()
    {
        $this->generateInheritedThemesNames('public');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/public/';
        $this->imagesFolder = 'images/';
        $this->imagesPath = PUBLIC_PATH . $this->imagesFolder;
    }
}
