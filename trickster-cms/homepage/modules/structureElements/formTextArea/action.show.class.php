<?php

class showFormTextArea extends structureElementAction
{
    /**
     * @param formTextAreaElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
        $structureElement->dataChunk = 'text';
    }
}

