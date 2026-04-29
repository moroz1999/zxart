<?php

class showLinkList extends structureElementAction
{
    /**
     * @param linkListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($fixedElement = $structureElement->getFixedElement()) {
            if ($structureElement->title == '') {
                $structureElement->title = $fixedElement->title;
            }

            $structureElement->URL = $fixedElement->URL;
        }
        $structureElement->setViewName($structureElement->getCurrentLayout());
        $structureElement->linkItems = $structureManager->getElementsChildren($structureElement->id);
    }
}