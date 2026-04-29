<?php

class seoElement extends structureElement
{
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = [
        "redirects",
        "errorPage",
        "notFoundLog",
        "searchLog",
    ];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }
}

