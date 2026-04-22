<?php

class showAiFormZxProd extends structureElementAction
{
    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('aiForm');
    }
}