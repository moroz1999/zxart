<?php

declare(strict_types=1);

class showStats extends structureElementAction
{
    /**
     * @param statsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested === true) {
            $structureElement->setViewName('content');
        }
    }
}

