<?php

class systemElement extends structureElement
{
    use AutoMarkerTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = [
        "translations",
        "settings",
        "adminTranslations",
        "adminLanguages",
        'actionsLog',
        'dispatchmentLog',
        'logViewer',
        'deployments',
        'translationsExport',
        'registrationFields',
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

