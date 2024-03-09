<?php

class banUser extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $parentElement = $structureManager->getElementsFirstParent($structureElement->id);
        $redirectURL = $parentElement->URL;

        if ($controller->getParameter('view')) {
            $redirectURL .= 'view:' . $controller->getParameter('view') . '/';
        }

        $structureElement->ban();
        $controller->redirect($redirectURL);
    }
}


