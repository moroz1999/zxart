<?php

class receiveLayoutSubMenuList extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param subMenuListElement $structureElement
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
            'layout',
            'columnLayout',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


