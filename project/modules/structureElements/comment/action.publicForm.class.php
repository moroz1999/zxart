<?php

class publicFormComment extends structureElementAction
{
    /**
     * @param commentElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if (!$structureElement->isEditable()) {
            $controller->redirect($structureElement->getInitialTarget()->getUrl());
            return;
        }
        $structureElement->setViewName('form');
    }
}
