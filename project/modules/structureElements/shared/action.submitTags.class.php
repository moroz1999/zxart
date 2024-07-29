<?php

class submitTagsShared extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->updateTagsInfo();
            $structureElement->persistElementData();

            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->setViewName('form');
        }
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'tagsText',
        ];
    }

}

