<?php

class publicDeleteShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $parentElement = $structureManager->getElementsFirstParent($structureElement->getId());
        $redirectURL = $parentElement->URL;

        $structureElement->deleteElementData();

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}