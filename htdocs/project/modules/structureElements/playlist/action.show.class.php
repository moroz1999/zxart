<?php

class showPlaylist extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
        } else {
            $structureElement->setViewName('short');
        }
    }
}

