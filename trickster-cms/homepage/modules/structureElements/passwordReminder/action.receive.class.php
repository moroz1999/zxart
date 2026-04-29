<?php

class receivePasswordReminder extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param passwordReminderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureTitle = $structureElement->title;
            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'content',
            'failMessage',
            'successMessage',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


