<?php

class receiveParser extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $controller->restart($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


