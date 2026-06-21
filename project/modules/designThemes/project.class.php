<?php

class projectDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    /**
     * @return void
     */
    public function initialize()
    {
        $this->generateInheritedThemesNames('public');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/public/';
        $this->cssPath = $controller->getProjectPath() . 'css/public/';
        $this->javascriptPath = $controller->getProjectPath() . 'js/public/';
        $this->javascriptUrl = '/project/js/public/';
        $this->imagesFolder = 'images/';
        $this->imagesPath = PUBLIC_PATH . $this->imagesFolder;
        $this->javascriptFiles = [
            'logics.zxPictures.js',
            'logics.chart.js',
            'logics.playlist.js',
            'basic.libgif.js',
            'logics.groupForm.js',
            'logics.authorForm.js',
            'logics.authorAliasForm.js',
            'logics.groupAliasForm.js',
            'logics.zxItemForm.js',
            'logics.partyForm.js',
            'logics.formHelper.js',
            'logics.editingControls.js',
            'logics.prods.js',
            'tape2Wav.js',
            'logics.zxReleases.js',
            'mixin.autoAdjustedPopup.js',
            'component.pictureTagsForm.js',
            'component.pictureDetails.js',
            'component.chart.js',
            'component.flickerImage.js',
            'component.groupForm.js',
            'component.authorForm.js',
            'component.authorAliasForm.js',
            'component.groupAliasForm.js',
            'component.zxItemForm.js',
            'component.partyForm.js',
            'component.formHelper.js',
            'component.DeleteButton.js',
            'component.EditingControls.js',
            'component.userPlaylistTableItemComponent.js',
            'component.playlistControls.js',
            'component.SettingsBlock.js',
            'component.ZxReleaseDetails.js',
        ];
    }
}
