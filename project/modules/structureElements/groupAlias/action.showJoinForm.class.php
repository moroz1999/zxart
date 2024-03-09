<?php

class showJoinFormGroupAlias extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('joinForm');
    }
}


