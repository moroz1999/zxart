<?php

class showPartiesList extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $type = 'latest';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
    }
}

