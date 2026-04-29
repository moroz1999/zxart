<?php

class receiveLayoutFolder extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param folderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->persistElementData();
            $controller->redirect($structureElement->getUrl("showLayoutForm"));
        }
        $structureElement->executeAction("showLayoutForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'colorLayout',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


