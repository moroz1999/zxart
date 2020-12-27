<?php

class receiveSeoZxProdCategory extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            $structureElement->persistElementData();
            $controller->redirect($structureElement->getUrl('showSeoForm'));
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
            'metaDescriptionTemplate',
            'metaTitleTemplate',
            'metaH1Template',
        ];
    }
}