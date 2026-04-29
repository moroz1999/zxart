<?php

class homepageAdminDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $controller = controller::getInstance();
        $pathsManager = $controller->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');
        $this->cssPath = $tricksterPath . 'homepage/css/admin/';
        $this->templatesFolder = $tricksterPath . 'homepage/templates/admin/';
        $this->imagesFolder = 'homepage/images/admin/';
        $this->imagesPath = $tricksterPath . $this->imagesFolder;
        $this->javascriptUrl = $controller->baseURL . $pathsManager->getRelativePath('trickster') . 'homepage/js/admin/';
        $this->javascriptPath = $tricksterPath . 'homepage/js/admin/';
        $this->javascriptFiles = [
            'logics.languagesForm.js',
            'logics.linkListItemForm.js',
            'logics.linkListForm.js',
            'logics.redirectForm.js',
            'logics.submenuListForm.js',
            'component.languagesForm.js',
            'component.linkListItemForm.js',
            'component.linkListForm.js',
            'component.redirectForm.js',
            'component.submenuListForm.js',
            'component.spoiler.js',
            'logics.visitor.js',
            'logics.showFilters.js',
            'component.showFilters.js',
            'logics.actionsButtons.js',
            'component.actionsButtons.js',
        ];
    }
}
