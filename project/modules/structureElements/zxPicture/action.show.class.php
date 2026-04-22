<?php

class showZxPicture extends structureElementAction
{
    /**
     * @param zxPictureElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
            $structureElement->logView();
        }
    }
}

