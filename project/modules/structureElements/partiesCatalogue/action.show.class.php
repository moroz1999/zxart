<?php

class showPartiesCatalogue extends structureElementAction
{
    /**
     * @param partiesCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('show');
        if (($firstParent = $structureElement->getFirstParentElement()) && $firstParent->requested) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('yearsInfo', $structureElement->getYearsSelectorInfo());
        }
    }
}

