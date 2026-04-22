<?php

class showJoinFormAuthor extends structureElementAction
{
    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('joinForm');
    }
}


