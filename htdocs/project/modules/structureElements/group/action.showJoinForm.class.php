<?php

class showJoinFormGroup extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('joinForm');
    }
}


