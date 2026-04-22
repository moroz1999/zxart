<?php

class receiveYear extends structureElementAction
{
    /**
     * @param yearElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $controller->restart($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = ['title'];
    }

    public function setValidators(&$validators): void
    {
        $validators['title'][] = 'notEmpty';
    }
}


