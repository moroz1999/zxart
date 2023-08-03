<?php

class showZxPicture extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
            $structureElement->logView();
        }
    }
}

