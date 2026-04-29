<?php

class showRegistrationInput extends structureElementAction
{
    /**
     * @param registrationInputElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
        $structureElement->dataChunk = 'text';
    }
}

