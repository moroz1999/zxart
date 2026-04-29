<?php

class receiveUserGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param userGroupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->groupName;
            $structureElement->persistElementData();
            $structureElement->setViewName('result');
            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setValidators(&$validators)
    {
        $validators['groupName'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'groupName',
            'description',
            'marker',
        ];
    }
}
