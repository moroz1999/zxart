<?php

class showSplitFormZxProd extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('splitForm');
    }
}