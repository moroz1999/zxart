<?php

class showUserPlaylists extends structureElementAction
{
    /**
     * @param userPlaylistsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $structureElement->getPlaylists();
        }
        $structureElement->setViewName('show');
    }
}

