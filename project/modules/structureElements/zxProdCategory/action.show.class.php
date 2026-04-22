<?php

class showZxProdCategory extends structureElementAction
{
    /**
     * @param zxProdCategoryElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
        } else {
            $structureElement->setViewName('list');
        }
    }
}