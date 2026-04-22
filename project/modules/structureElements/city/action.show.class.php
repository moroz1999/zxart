<?php

class showCity extends structureElementAction
{
    /**
     * @param cityElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('content');
    }
}

