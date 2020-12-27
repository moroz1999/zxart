<?php

class showYear extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param yearElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $renderer = $this->getService('renderer');
            $renderer->assign('yearsInfo', $structureElement->getYearsSelectorInfo());
            if ($structureElement->final) {
                $structureElement->setViewName('show');
            }
        }
    }
}

