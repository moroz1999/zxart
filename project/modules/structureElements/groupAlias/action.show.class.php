<?php

class showGroupAlias extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
//            if (!$worksList = $structureElement->getWorksList()) {
//                if ($groupElement = $structureElement->getGroupElement()) {
//                    $controller->redirect($groupElement->getUrl());
//                }
//            }
            $structureElement->setViewName('details');
        }
    }
}
