<?php

class showCountriesList extends structureElementAction
{
    /**
     * @param countriesListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('content');
    }
}

