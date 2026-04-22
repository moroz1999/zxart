<?php

class recalculateShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->recalculate();
        $structureElement->executeAction('show');
        foreach ($structureElement->getRealAuthorsList() as $authorElement) {
            if (is_object($authorElement) && $authorElement->structureType == 'author') {
                $authorElement->executeAction('recalculate');
            }
        }
    }
}