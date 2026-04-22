<?php

class showYear extends structureElementAction
{
    /**
     * @param yearElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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

