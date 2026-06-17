<?php

class receiveFeedback extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated === true) {
            $structureElement->prepareActualData();
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();
            $structureElement->persistDisplayMenusLinks();
            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction('showForm');
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'destination',
            'content',
            'displayMenus',
        ];
    }

    public function setValidators(&$validators)
    {
        $validators['destination'][] = 'email';
    }
}
