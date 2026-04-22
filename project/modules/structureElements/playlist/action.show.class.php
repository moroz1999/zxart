<?php

class showPlaylist extends structureElementAction
{
    /**
     * @param playlistElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
        } else {
            $structureElement->setViewName('short');
        }
    }
}

