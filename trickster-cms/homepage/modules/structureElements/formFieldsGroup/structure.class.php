<?php

class formFieldsGroupElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    protected $allowedTypes = [
        'formInput',
        'formDateInput',
        'formTextArea',
        'formCheckBox',
        'formSelect',
        'formFileInput',
    ];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $formFieldsList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getFormFields()
    {
        if (is_null($this->formFieldsList)) {
            $this->formFieldsList = [];

            $structureManager = $this->getService('structureManager');
            if ($fields = $structureManager->getElementsChildren($this->id)) {
                $this->formFieldsList = $fields;
            }
        }
        return $this->formFieldsList;
    }
}

