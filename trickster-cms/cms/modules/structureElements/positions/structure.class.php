<?php

class positionsElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $positionElements = [];

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['positions'] = 'numbersArray';
    }

    public function getPositionElements()
    {
        $linksManager = $this->getService(linksManager::class);
        $structureManager = $this->getService('structureManager');
        $currentElement = $structureManager->getCurrentElement();

        $structureManager->getElementsChildren($currentElement->id);

        $positionElements = [];
        if ($parentLinks = $linksManager->getElementsLinks($currentElement->id, '', 'parent')) {
            foreach ($parentLinks as $link) {
                //todo: change hardcode to config
                if ($link->type != 'displayinmenu' && $link->type != 'displayinmenumobile') {
                    $childElement = $structureManager->getElementById($link->childStructureId);
                    if ($childElement && $childElement->structureType !== 'positions') {
                        $childElement->position = $link->position;
                        if (!isset($positionElements[$link->type])) {
                            $positionElements[$link->type] = [];
                        }
                        $positionElements[$link->type][] = $childElement;
                    }
                }
            }
        }
        ksort($positionElements);

        return $positionElements;
    }
}