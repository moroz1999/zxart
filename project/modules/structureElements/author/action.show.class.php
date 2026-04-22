<?php

class showAuthor extends structureElementAction
{
    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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