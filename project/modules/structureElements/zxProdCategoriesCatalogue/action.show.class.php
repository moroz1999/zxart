<?php

class showZxProdCategoriesCatalogue extends structureElementAction
{
    /**
     * @param zxProdCategoriesCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('details');
    }
}

