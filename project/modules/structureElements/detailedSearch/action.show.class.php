<?php

class showDetailedSearch extends structureElementAction
{
    /**
     * @param detailedSearchElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('form');
        }
    }
}