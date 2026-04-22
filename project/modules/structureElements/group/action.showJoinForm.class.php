<?php

class showJoinFormGroup extends structureElementAction
{
    /**
     * @param groupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('joinForm');
    }
}


