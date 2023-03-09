<?php

class picturesUploadFormElement extends ZxArtItemUploadFormElement
{
    use GraphicsCompoProvider;
    use AuthorElementsProviderTrait;
    use PartyElementProviderTrait;
    use ZxPictureTypesProvider;

    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'batchUploadForm';
    public $role = 'container';

    protected $gamesList;
    protected $partiesList;
    protected $authorsList;
    protected $authorsIDList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';

        $moduleStructure['pictureTitle'] = 'text';
        $moduleStructure['description'] = 'pre';
        $moduleStructure['border'] = 'text';
        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';

        $moduleStructure['image'] = 'files';

        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['rotation'] = 'text';
        $moduleStructure['palette'] = 'text';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }
}


