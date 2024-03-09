<?php

class showGroup extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            if ($controller->getParameter('view') == 'wiki') {
                $structureElement->setViewName('wiki');
            } else {
                $structureElement->setViewName('details');
            }
        }
    }
}