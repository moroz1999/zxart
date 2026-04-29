<?php

class showErrorPage extends structureElementAction
{
    /**
     * @param errorPageElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setTemplate('errorPage.show.tpl');
    }
}