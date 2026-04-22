<?php

class showParser extends structureElementAction
{
    /**
     * @param parserElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('show');
    }
}

