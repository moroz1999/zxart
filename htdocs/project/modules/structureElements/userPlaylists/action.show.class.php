<?php

class showUserPlaylists extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $structureElement->getPlaylists();
        }
        $structureElement->setViewName('show');
    }
}

