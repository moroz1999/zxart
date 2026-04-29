<?php

class showFormFieldsGroup extends structureElementAction
{
    /**
     * @param formFieldsGroupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
    }
}

