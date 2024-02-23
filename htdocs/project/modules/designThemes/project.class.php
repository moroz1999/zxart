<?php

class projectDesignTheme extends DesignTheme
{
    use InheritedThemesTrait;

    public function initialize()
    {
        $this->generateInheritedThemesNames('public');

        $controller = controller::getInstance();
        $this->templatesFolder = $controller->getProjectPath() . 'templates/public/';
        $this->cssPath = $controller->getProjectPath() . 'css/public/';
        $this->javascriptPath = $controller->getProjectPath() . 'js/public/';
        $this->javascriptUrl = '/project/js/public/';
        $this->imagesFolder = 'project/images/public/';
        $this->imagesPath = ROOT_PATH . $this->imagesFolder;
        $this->javascriptFiles = [
            'logics.vote.js',
            'logics.zxPictures.js',
            'logics.detailedSearch.js',
            'logics.chart.js',
            'logics.broadcast.js',
            'logics.music.js',
            'logics.playlist.js',
            'basic.libgif.js',
            'logics.artWebZXGallery.js',
            'logics.stagesAnimation.js',
            'logics.groupForm.js',
            'logics.authorForm.js',
            'logics.authorAliasForm.js',
            'logics.groupAliasForm.js',
            'logics.zxItemForm.js',
            'logics.partyForm.js',
            'logics.formHelper.js',
            'logics.editingControls.js',
            'logics.radioControls.js',
            'logics.prods.js',
            'logics.musicPlayer.js',
            'logics.musicLogger.js',
            'logics.musicRadio.js',
            'logics.zxMap.js',
            'tape2Wav.js',
            'logics.zxReleases.js',
            'mixin.autoAdjustedPopup.js',
            'component.pictureTagsForm.js',
            'component.pictureDetails.js',
            'component.vote.js',
            'component.detailedSearch.js',
            'component.chart.js',
            'component.musicShort.js',
            'component.emulator.js',
            'component.artWebZXGallery.js',
            'component.stagesAnimation.js',
            'component.flickerImage.js',
            'component.musicFull.js',
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
            'component.ZxMap.js',
            'component.ZxReleaseDetails.js',
            'component.PurchaseButton.js',
            'ng-zxart/main.js',
            'ng-zxart/polyfills.js',
            'ng-zxart/runtime.js',
        ];
    }
}
