<?php

class logsElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = [
        "notFoundLog",
        "dispatchmentLog",
        "searchLog",
        "payments",
        "actionsLog",
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