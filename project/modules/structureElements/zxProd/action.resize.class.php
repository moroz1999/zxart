<?php

class resizeZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->resizeImages();
        $structureElement->setViewName('details');
//        $controller->redirect($structureElement->URL);
    }
}