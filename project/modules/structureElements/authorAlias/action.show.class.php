<?php

class showAuthorAlias extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorAliasElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setViewName('details');
        }
    }
}