<?php

class votesListElement extends structureElement
{
    public $dataResourceName = 'module_voteslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
    }
}


