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

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'showForm'}
     */
    protected function getTabsList()
    {
        return [
            'showForm',
        ];
    }
}