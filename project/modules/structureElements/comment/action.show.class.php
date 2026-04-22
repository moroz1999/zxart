<?php

class showComment extends structureElementAction
{
    /**
     * @param commentElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $structureElement->setViewName('short');
        }
    }
}