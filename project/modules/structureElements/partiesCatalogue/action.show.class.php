<?php

class showPartiesCatalogue extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param partiesCatalogueElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('show');
        if (($firstParent = $structureElement->getFirstParentElement()) && $firstParent->requested) {
            $renderer = $this->getService('renderer');
            $renderer->assign('yearsInfo', $structureElement->getYearsSelectorInfo());
        }
    }
}

