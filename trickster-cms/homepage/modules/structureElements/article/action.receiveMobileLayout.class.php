<?php

class receiveMobileLayoutArticle extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param articleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'mobileLayout',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


