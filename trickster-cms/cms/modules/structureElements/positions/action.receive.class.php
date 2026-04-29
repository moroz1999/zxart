<?php

class receivePositions extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param positionsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $linksManager = $this->getService(linksManager::class);
        if ($currentElement = $structureManager->getCurrentElement()) {
            $structureManager->getElementsChildren($currentElement->id);

            $parentLinks = $linksManager->getElementsLinks($currentElement->id, '', 'parent');

            foreach ($parentLinks as $link) {
                if (isset($structureElement->positions[$link->childStructureId])) {
                    $link->position = $structureElement->positions[$link->childStructureId];
                    $link->persist();
                }
            }
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['positions'];
    }
}
