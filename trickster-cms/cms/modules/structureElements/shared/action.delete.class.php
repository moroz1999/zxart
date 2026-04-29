<?php

class deleteShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $redirectURL = false;
        if (!$structureElement->groupDeletion) {
            $parentElement = $structureManager->getElementsFirstParent($structureElement->id);
            $redirectURL = $parentElement->URL;

            if ($controller->getParameter('view')) {
                $redirectURL .= 'view:' . $controller->getParameter('view') . '/';
            }
        }
        $structureElement->deleteElementData();

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}


