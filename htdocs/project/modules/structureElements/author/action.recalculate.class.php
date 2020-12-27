<?php

class recalculateAuthor extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->recalculateAuthorData();
    }
}

