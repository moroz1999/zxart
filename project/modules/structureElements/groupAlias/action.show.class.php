<?php

class showGroupAlias extends structureElementAction
{
    /**
     * @param groupAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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
