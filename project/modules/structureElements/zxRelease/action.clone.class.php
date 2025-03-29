<?php

use ZxArt\Prods\Services\ProdsService;

class cloneZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     *
     * @return void
     * @throws Exception
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $parentElement = $structureElement->getFirstParentElement();
        if ($parentElement === null) {
            throw new Exception('Parent element is null for cloning ' . $structureElement->id . ' ' . $structureElement->getTitle());
        }
        $clonedElements = $structureManager->copyElements([$structureElement->id], $parentElement->id);
        if ($clonedElements === []) {
            throw new Exception('Release cloning failed for ' . $structureElement->id . ' ' . $structureElement->getTitle());
        }
        $clonedElementId = reset($clonedElements);
        $clonedElement = $structureManager->getElementById($clonedElementId);
        if ($clonedElement === null) {
            throw new Exception('Release cloning failed for ' . $structureElement->id . ' ' . $structureElement->getTitle());
        }
        /**
         * @var zxReleaseElement $clonedElement
         */
        $clonedElement->hardwareRequired = $structureElement->hardwareRequired;
        $clonedElement->language = $structureElement->language;
        $clonedElement->publishers = $structureElement->publishers;
        $clonedElement->downloads = 0;
        $clonedElement->plays = 0;
        $clonedElement->persistElementData();

        $prodsService = $this->getService(ProdsService::class);
        $prodsService->copyAuthorship($structureElement, $clonedElement);

        $controller->redirect($clonedElement->getUrl());
    }
}


