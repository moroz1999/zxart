<?php

class showFile extends structureElementAction
{
    /**
     * @param fileElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            if ($parent = $structureManager->getElementsFirstParent($structureElement->id)) {
                $controller->redirect($parent->URL, 301);
            }
        }
    }
}