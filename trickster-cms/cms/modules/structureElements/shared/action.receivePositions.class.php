<?php

class receivePositionsShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->positionsForm = $structureManager->createElement('positions', 'receive', $structureElement->id)
        ) {
            $structureElement->setViewName('positions');
            $controller->redirect($structureElement->getFormActionURL() . 'id:' . $structureElement->id . '/action:showPositions/');
        }
    }
}