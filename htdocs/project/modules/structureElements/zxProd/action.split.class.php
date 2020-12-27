<?php

class splitZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var ProdsManager $prodsManager
             */
            $prodsManager = $this->getService('ProdsManager');

            if ($structureElement->splitData) {
                if ($newElement = $prodsManager->splitZxProd($structureElement->id, $structureElement->splitData)) {
                    $controller->redirect($newElement->getUrl());
                }
            }
        }

        $structureElement->setViewName('splitForm');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'splitData',
        ];
    }
}