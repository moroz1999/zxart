<?php

class showStats extends structureElementAction
{
    /**
     * @param statsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('content');
    }
}

