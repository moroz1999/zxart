<?php

class banUser extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param userElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $parentElement = $structureManager->getElementsFirstParent($structureElement->getId());
        $redirectURL = $parentElement->URL;

        if ($controller->getParameter('view')) {
            $redirectURL .= 'view:' . $controller->getParameter('view') . '/';
        }

        $structureElement->ban();
        $controller->redirect($redirectURL);
    }
}


