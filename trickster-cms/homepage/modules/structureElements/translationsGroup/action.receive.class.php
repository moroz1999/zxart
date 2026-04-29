<?php

class receiveTranslationsGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param translationsGroupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
            $structureElement->setViewName('result');
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['title'];
    }
}

