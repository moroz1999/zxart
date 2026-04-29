<?php

class receiveLayoutLinkList extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param linkListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->persistElementData();
            $controller->redirect($structureElement->getUrl('showLayoutForm'));
        }
        $structureElement->executeAction("showLayoutForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'layout',
            'cols',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}