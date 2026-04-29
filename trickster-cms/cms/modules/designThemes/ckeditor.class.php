<?php

class ckeditorDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $pathsManager = controller::getInstance()->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'cms/css/';
        $this->cssFiles = [];
        $this->imagesFolder = 'images/';
        $this->imagesPath = ROOT_PATH . 'trickster/cms/' . $this->imagesFolder;
        $this->imagesPaths = [
            $this->imagesPath,
            ROOT_PATH . 'project/' . $this->imagesFolder ,
        ];
    }

    public function getCssResources()
    {
        if (is_null($this->cssResources)) {
            $configurationManager = controller::getInstance()->getConfigManager();
            $publicThemeName = $configurationManager->get('main.publicTheme');
            $this->cssResources = parent::getCssResources();

            $this->appendCssResourceFromInheritedThemes('reset.less', $publicThemeName);

            $this->appendCssResourceFromTheme('all_mixins.less', 'default');

            $this->appendCssResourceFromInheritedThemes('variables.less', $publicThemeName);
            $this->appendCssResourceFromTheme('variables.less', $publicThemeName);
            $this->appendCssResourceFromTheme('all_variables.less', $publicThemeName);
            $this->appendCssResourceFromTheme('all_variables.less', 'default');

            $this->appendCssResourceFromInheritedThemes('all_constants.less', $publicThemeName);
            $this->appendCssResourceFromTheme('all_constants.less', $publicThemeName);

            $this->appendCssResourceFromTheme('shared.less', $publicThemeName);

            $this->appendCssResourceFromInheritedThemes('component.table.less', $publicThemeName);
            $this->appendCssResourceFromTheme('component.table.less', $publicThemeName);

            $this->appendCssResourceFromInheritedThemes('component.ckeditor_templates.less', $publicThemeName);
            $this->appendCssResourceFromTheme('component.ckeditor_templates.less', $publicThemeName);
        }
        return $this->cssResources;
    }
}