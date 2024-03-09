<?php

class submitTagsShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            $structureElement->updateTagsInfo();
            $structureElement->persistElementData();

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->setViewName('form');
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'tagsText',
        ];
    }

}

