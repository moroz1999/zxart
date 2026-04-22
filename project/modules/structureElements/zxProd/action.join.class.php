<?php

use ZxArt\Prods\Services\ProdsService;

class joinZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $prodsService = $this->getService(ProdsService::class);

            if ($structureElement->joinAndDelete) {
                $prodsService->joinDeleteZxProd($structureElement->getId(), $structureElement->joinAndDelete, $structureElement->releasesOnly);
            }

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAndDelete',
            'releasesOnly',
        ];
    }
}


