<?php

class receiveSearch extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param searchElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->hidden = '1';
            if ($structureElement->title != '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();

            $controller->redirect($structureElement->URL);
            $structureElement->setViewName('result');
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'displayMenus',
            'bAjaxSearch',
        ];
    }
}