<?php

class publicDeleteShared extends structureElementAction
{
    //    public function execute(&$structureManager, &$controller, &$structureElement)
    //    {
    //        $structureManager = $this->getService('structureManager');
    //        if ($userId = $structureManager->getElementIdByMarker('userGroup-public')) {
    //            $privilegesManager = $this->getService('privilegesManager');
    //
    //            $privilegesManager->setPrivilege($userId, $structureElement->getId(), $structureElement->structureType, 'show', 'deny');
    //            $privilegesManager->resetPrivileges();
    //
    //            $user = $this->getService(user::class);
    //            $user->refreshPrivileges();
    //
    //            if ($parentElement = $structureManager->getElementsFirstParent($structureElement->getId())) {
    //                $controller->redirect($parentElement->URL);
    //            }
    //        }
    //    }
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $parentElement = $structureManager->getElementsFirstParent($structureElement->getId());
        $redirectURL = $parentElement->URL;

        $structureElement->deleteElementData();

        if ($redirectURL) {
            $controller->redirect($redirectURL);
        }
    }
}