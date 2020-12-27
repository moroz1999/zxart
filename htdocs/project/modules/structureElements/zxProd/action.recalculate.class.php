<?php

class recalculateZxProd extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->recalculateZxProdData();
    }
}

