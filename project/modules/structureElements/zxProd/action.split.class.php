<?php

use ZxArt\Prods\Services\ProdsService;

class splitZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $prodsService = $this->getService(ProdsService::class);

            if ($structureElement->splitData) {
                if ($newElement = $prodsService->splitZxProd($structureElement->getId(), $structureElement->splitData)) {
                    $controller->redirect($newElement->getUrl());
                }
            }
        }

        $structureElement->setViewName('splitForm');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'splitData',
        ];
    }
}