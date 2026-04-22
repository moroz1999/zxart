<?php

class showCountry extends structureElementAction
{
    /**
     * @param countryElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('content');
    }
}

