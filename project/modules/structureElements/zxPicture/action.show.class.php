<?php

class showZxPicture extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
            $structureElement->logView();
        }
    }
}

