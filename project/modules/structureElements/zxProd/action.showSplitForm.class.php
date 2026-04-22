<?php

class showSplitFormZxProd extends structureElementAction
{
    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('splitForm');
    }
}