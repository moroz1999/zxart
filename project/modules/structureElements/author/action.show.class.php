<?php

class showAuthor extends structureElementAction
{
    /**
     * @return void
     */
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