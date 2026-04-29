<?php

class showSubMenuList extends structureElementAction
{
    /**
     * @param subMenuListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('block');
    }
}

