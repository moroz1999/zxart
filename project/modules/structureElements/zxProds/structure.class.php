<?php

class zxProdsElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['zxProd'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

}