<?php

class formTextAreaElement extends formFieldStructureElement
{
    use AutocompleteOptionsTrait;
    public $dataResourceName = 'module_form_field';
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['fieldName'] = 'text';
        $moduleStructure['fieldType'] = 'text';
        $moduleStructure['dataChunk'] = 'text';
        $moduleStructure['required'] = 'checkbox';
        $moduleStructure['validator'] = 'text';
        $moduleStructure['autocomplete'] = 'text';
        $moduleStructure['placeholder'] = 'text';
    }
}