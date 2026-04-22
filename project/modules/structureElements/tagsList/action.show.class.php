<?php

class showTagsList extends structureElementAction
{
    /**
     * @param tagsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('content');
    }
}

