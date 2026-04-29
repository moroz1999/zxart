<?php

class linkListItemElement extends structureElement implements ConfigurableLayoutsElementsInterface
{
    use SearchTypesProviderTrait;
	use ImageUrlProviderTrait;
    public $dataResourceName = 'module_linklist_item';
    public $defaultActionName = 'show';
    public $role = 'content';
    public $connectedMenu;
    protected $fixedElement;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['content'] = 'html';
        $moduleStructure['link'] = 'url';
        $moduleStructure['linkText'] = 'text';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['fixedId'] = 'text';
        $moduleStructure['highlighted'] = 'checkbox';
        $moduleStructure['secondaryImage'] = 'image';
        $moduleStructure['secondaryImageOriginalName'] = 'fileName';
        $moduleStructure['tertiaryImage'] = 'image';
        $moduleStructure['tertiaryImageOriginalName'] = 'fileName';
        $moduleStructure['quaternaryImage'] = 'image';
        $moduleStructure['quaternaryImageOriginalName'] = 'fileName';
    }

    public function isLinkInternal()
    {
        $controller = controller::getInstance();
        if ($this->fixedId || substr($this->link, 0, 1) == "/" || stripos($this->link, $controller->domainName) !== false
        ) {
            return true;
        }
        return false;
    }

    public function getTitle()
    {
        if ($fixedElement = $this->getFixedElement()) {
            return $fixedElement->getTitle();
        }
        return parent::getTitle();
    }

    public function getFixedElement()
    {
        if ($this->fixedElement === null && $this->fixedId) {
            $structureManager = $this->getService('structureManager');
            $this->fixedElement = $structureManager->getElementById($this->fixedId);
        }
        return $this->fixedElement;
    }

    public function getLayoutProviders()
    {
        $structureManager = $this->getService('structureManager');
        $connectedElements = $structureManager->getElementsParents($this->id);

        if ($fixedElement = $this->getFixedElement()) {
            array_unshift($connectedElements, $fixedElement);
        }
        return $connectedElements;
    }
}