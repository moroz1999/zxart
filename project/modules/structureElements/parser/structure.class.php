<?php

/**
 * Class parserElement
 */
class parserElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function getTabsList()
    {
        return [
            'showForm',
        ];
    }
}