<?php

class receiveZxItemsList extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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

