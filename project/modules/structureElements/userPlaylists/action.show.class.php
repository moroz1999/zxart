<?php

class showUserPlaylists extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $structureElement->getPlaylists();
        }
        $structureElement->setViewName('show');
    }
}

