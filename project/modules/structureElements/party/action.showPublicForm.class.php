<?php

class showPublicFormParty extends structureElementAction
{
    /**
     * @param partyElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
    }
}


