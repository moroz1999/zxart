<?php

class zxProdCategoriesElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['zxProdCategory'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

}