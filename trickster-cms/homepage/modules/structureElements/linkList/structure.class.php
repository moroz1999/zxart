<?php

class linkListElement extends menuDependantStructureElement implements ConfigurableLayoutsProviderInterface
{
    use ConfigurableLayoutsProviderTrait;
    use SearchTypesProviderTrait;
    public $dataResourceName = 'module_linklist';
    protected $allowedTypes = ['linkListItem'];
    public $defaultActionName = 'show';
    public $role = 'content';
    public $linkItems = [];
    public $connectedMenu;
    protected $fixedElement;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['layout'] = 'text';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['fixedId'] = 'text';
        $moduleStructure['content'] = 'html';
        $moduleStructure['cols'] = 'naturalNumber';
    }

    protected function getTabsList()
    {
        return [
            'showForm',
            'showLayoutForm',
            'showPositions',
            'showPrivileges',
        ];
    }

    public function getFixedElement()
    {
        if ($this->fixedElement === null && $this->fixedId) {
            $structureManager = $this->getService('structureManager');
            $this->fixedElement = $structureManager->getElementById($this->fixedId);
        }
        return $this->fixedElement;
    }
}