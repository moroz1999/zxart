<?php

class adminDesignTheme extends DesignTheme
{
    public function initialize()
    {
        $this->inheritedThemes = ['default'];
        $controller = controller::getInstance();
        $pathsManager = $controller->getPathsManager();
        $tricksterPath = $pathsManager->getPath('trickster');

        $this->cssPath = $tricksterPath . 'cms/css/admin/';
        $this->templatesFolder = $tricksterPath . 'cms/templates/admin/';
        $this->imagesFolder = 'cms/images/admin/';
        $this->imagesPath = $tricksterPath . $this->imagesFolder;
        $this->fontsFolder = 'cms/fonts/';
        $this->fontsPath = $tricksterPath . $this->fontsFolder;
        $this->fontsUrl = $controller->baseURL . $pathsManager->getRelativePath('trickster') . $this->fontsFolder;
        $this->javascriptUrl = $controller->baseURL . $pathsManager->getRelativePath('trickster') . 'cms/js/admin/';
        $this->javascriptPath = $tricksterPath . 'cms/js/admin/';

        $this->javascriptFiles = [
            'logics.addNewElement.js',
            'logics.contentFilterForm.js',
            'logics.contentList.js',
            'logics.dropDown.js',
            'logics.formHelper.js',
            'logics.genericForm.js',
            'logics.groupBox.js',
            'logics.privilegesForm.js',
            'logics.tabsBlock.js',
            'logics.translationForm.js',
            'logics.chart.js',
            'logics.radioTabs.js',
            'logics.mobile_control.js',
            'logics.animation.js',
            'logics.tableComponent.js',
            'logics.imagePreview.js',
            'logics.jsColor.js',
            'logics.calendarSelector.js',
            'component.tableComponent.js',
            'component.addNewElement.js',
            'component.ajaxItemSearch.js',
            'component.contentFilterForm.js',
            'component.contentList.js',
            'component.dropDown.js',
            'component.formHelper.js',
            'component.genericForm.js',
            'component.groupBox.js',
            'component.headerAjaxSearch.js',
            'component.privilegesForm.js',
            'component.tabsBlock.js',
            'component.translationForm.js',
            'component.chart.js',
            'component.radioTabs.js',
            'component.pager.js',
            'component.animation.js',
            'component.imagePreview.js',
            'component.FormControlsButton.js',
            'component.calendarSelector.js',
            'triangles.js',
        ];
    }
}