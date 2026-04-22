<?php

class receiveParser extends structureElementAction
{
    /**
     * @param parserElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $controller->restart($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


