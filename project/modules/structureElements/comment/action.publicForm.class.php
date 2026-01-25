<?php

class publicFormComment extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if (!$structureElement->isEditable()) {
            $controller->redirect($structureElement->getInitialTarget()->getUrl());
            return;
        }
        $structureElement->setViewName('form');
    }
}
