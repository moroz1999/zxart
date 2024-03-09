<?php

class zxProdCategoriesElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = ['zxProdCategory'];
    public $defaultActionName = 'showFullList';
    public $role = 'container';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

}