<?php

class showZxItemsList extends structureElementAction
{
    /**
     * @param zxItemsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $items = 'graphics';
        if ($structureElement->items) {
            $items = $structureElement->items;
        }
        $structureElement->setViewName($items);
    }
}