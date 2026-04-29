<?php

class receiveSeoShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction("showSeoForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'structureName',
            'metaTitle',
            'h1',
            'metaDescription',
            'canonicalUrl',
            'metaDenyIndex',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}