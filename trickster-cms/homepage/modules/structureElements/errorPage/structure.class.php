<?php

class errorPageElement extends structureElement
{
    public $dataResourceName = 'module_errorpage';
    public $defaultActionName = 'show';
    public $role = 'container';

    protected function getTabsList()
    {
        return [
            'showErrorPageForm',
        ];
    }

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['content'] = 'html';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'content';
    }
}