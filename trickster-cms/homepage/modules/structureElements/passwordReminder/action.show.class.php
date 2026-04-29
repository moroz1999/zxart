<?php

class showPasswordReminder extends structureElementAction
{
    /**
     * @param passwordReminderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->setViewName('form');
    }
}

