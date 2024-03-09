<?php

class receiveStats extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated === true) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
            $structureElement->setViewName('result');
        } else {
            $structureElement->executeAction('showForm');
        }
    }

    public function setValidators(&$validators): void
    {
        $validators['title'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = ['title'];
    }
}

