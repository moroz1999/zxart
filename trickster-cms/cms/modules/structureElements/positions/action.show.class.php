<?php

class showPositions extends structureElementAction
{
    /**
     * @param positionsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
    }
}
