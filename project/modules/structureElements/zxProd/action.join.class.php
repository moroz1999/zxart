<?php

class joinZxProd extends structureElementAction
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

            if ($structureElement->joinAndDelete) {
                $prodsManager->joinDeleteZxProd($structureElement->id, $structureElement->joinAndDelete, $structureElement->releasesOnly);
            }

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'joinAndDelete',
            'releasesOnly',
        ];
    }
}


