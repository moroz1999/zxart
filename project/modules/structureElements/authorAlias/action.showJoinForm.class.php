<?php

class showJoinFormAuthorAlias extends structureElementAction
{
    /**
     * @param authorAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('joinForm');
    }
}


