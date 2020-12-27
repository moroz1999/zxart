<?php

class showZxItemsList extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $items = 'graphics';
        if ($structureElement->items) {
            $items = $structureElement->items;
        }
        $structureElement->setViewName($items);
    }
}