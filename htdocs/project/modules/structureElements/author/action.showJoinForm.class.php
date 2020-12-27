<?php

class showJoinFormAuthor extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('joinForm');
    }
}


