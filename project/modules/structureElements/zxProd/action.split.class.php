<?php

use ZxArt\Prods\Services\ProdsService;

class splitZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var ProdsService $prodsManager
             */
            $prodsManager = $this->getService(ProdsService::class);

            if ($structureElement->splitData) {
                if ($newElement = $prodsManager->splitZxProd($structureElement->id, $structureElement->splitData)) {
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