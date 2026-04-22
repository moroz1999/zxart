<?php

class showZxMusic extends structureElementAction
{
    /**
     * @param zxMusicElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('details');
    }
}

