<?php

class receiveZxItemsList extends structureElementAction
{
    /**
     * @param zxItemsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();
            $controller->restart($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'type',
            'items',
            'apiString',
            'searchFormParametersString',
            'requiresUser',
            'buttonTitle',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}

