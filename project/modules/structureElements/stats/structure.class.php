<?php

declare(strict_types=1);

class statsElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';

    protected function setModuleStructure(&$moduleStructure): void
    {
        $moduleStructure['title'] = 'text';
    }
}
