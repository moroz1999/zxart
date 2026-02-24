<?php

class showYear extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param yearElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('yearsInfo', $structureElement->getYearsSelectorInfo());
            if ($structureElement->final) {
                $structureElement->setViewName('show');
            }
        }
    }
}

