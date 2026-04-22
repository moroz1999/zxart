<?php

class showJoinFormGroupAlias extends structureElementAction
{
    /**
     * @param groupAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('joinForm');
    }
}


