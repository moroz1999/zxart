<?php

class receiveFeedback extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->persistDisplayMenusLinks();
            $controller->redirect($structureElement->URL);
            $structureElement->setViewName('result');
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'destination',
            'content',
            'buttonTitle',
            'role',
            'displayMenus'
        ];
    }

    public function setValidators(&$validators)
    {
        $validators['destination'][] = 'email';
    }
}


