<?php

class showZxProdCategory extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdCategoryElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
        } else {
            $structureElement->setViewName('list');
        }
    }
}