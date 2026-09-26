<?php

class publicDeleteShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        // The parent has to be resolved while the element still exists.
        $parentElement = $structureManager->getElementsFirstParent($structureElement->getId());
        $redirectURL = $parentElement->URL;

        $structureElement->deleteElementData();

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}
