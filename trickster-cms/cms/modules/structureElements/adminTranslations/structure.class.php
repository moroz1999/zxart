<?php

class adminTranslationsElement extends TranslationsStructureElement
{
    use SortedChildrenListTrait;
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = ['adminTranslationsGroup'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function initialize()
    {
        $this->translationsLanguagesGroup = 'adminLanguages';
    }

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['xmlFile'] = 'file';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getThemeCodes()
    {
        return ['projectAdmin'];
    }
}
