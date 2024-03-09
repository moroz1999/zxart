<?php

class publicDeleteShared extends structureElementAction
{
    //    public function execute(&$structureManager, &$controller, &$structureElement)
    //    {
    //        $structureManager = $this->getService('structureManager');
    //        if ($userId = $structureManager->getElementIdByMarker('userGroup-public')) {
    //            $privilegesManager = $this->getService('privilegesManager');
    //
    //            $privilegesManager->setPrivilege($userId, $structureElement->id, $structureElement->structureType, 'show', 'deny');
    //            $privilegesManager->resetPrivileges();
    //
    //            $user = $this->getService('user');
    //            $user->refreshPrivileges();
    //
    //            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
    //                $controller->redirect($parentElement->URL);
    //            }
    //        }
    //    }
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $parentElement = $structureManager->getElementsFirstParent($structureElement->id);
        $redirectURL = $parentElement->URL;

        $structureElement->deleteElementData();

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}