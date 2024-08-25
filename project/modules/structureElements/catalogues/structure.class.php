<?php

class cataloguesElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $allowedTypes = [
        'groups',
        'ZxProdCategories',
        'prods',
    ];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
    }
}