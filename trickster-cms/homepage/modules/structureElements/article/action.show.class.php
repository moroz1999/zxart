<?php

class showArticle extends structureElementAction
{
    /**
     * @param articleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName($structureElement->getCurrentLayout('layout'));
        if ($structureElement->final) {
            if ($parent = $structureManager->getElementsFirstParent($structureElement->id)) {
                $controller->restart($parent->URL);
            }
        }
    }
}

