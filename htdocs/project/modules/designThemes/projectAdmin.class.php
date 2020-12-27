<?php

class projectAdminDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    public function initialize()
    {
        $this->generateInheritedThemesNames('admin');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/admin/';
        $this->cssPath = $controller->getProjectPath() . 'css/admin/';
        $this->javascriptPath = $controller->getProjectPath() . 'js/admin/';
        $this->javascriptUrl = '/project/js/admin/';
        $this->javascriptFiles = [
            'logics.tagForm.js',
            'logics.authorForm.js',
            'logics.groupForm.js',
            'logics.partyForm.js',
            'logics.zxItemForm.js',
            'logics.countryForm.js',
            'logics.cityForm.js',
            'logics.authorAliasForm.js',
            'component.joinTagForm.js',
            'component.redirectForm.js',
            'component.authorForm.js',
            'component.groupForm.js',
            'component.partyForm.js',
            'component.zxItemForm.js',
            'component.countryForm.js',
            'component.cityForm.js',
            'component.authorAliasForm.js',
        ];
        $this->imagesFolder = 'project/images/admin/';
        $this->imagesPath = ROOT_PATH . $this->imagesFolder;
    }
}