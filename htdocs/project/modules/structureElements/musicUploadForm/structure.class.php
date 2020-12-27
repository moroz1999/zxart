<?php

class musicUploadFormElement extends ZxArtItemUploadFormElement
{
    use AuthorElementsProviderTrait;
    use PartyElementProviderTrait;

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

        $moduleStructure['musicTitle'] = 'text';
        $moduleStructure['description'] = 'textarea';
        $moduleStructure['party'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';

        $moduleStructure['music'] = 'files';

        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['channelsType'] = 'text';
        $moduleStructure['chipType'] = 'text';
        $moduleStructure['frequency'] = 'text';
        $moduleStructure['intFrequency'] = 'text';
        $moduleStructure['formatGroup'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }
}


