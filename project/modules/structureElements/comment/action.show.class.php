<?php

class showComment extends structureElementAction
{
    /**
     * @param $structureManager
     * @param $controller
     * @param commentElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested) {
            $structureElement->setViewName('short');
        }
    }
}