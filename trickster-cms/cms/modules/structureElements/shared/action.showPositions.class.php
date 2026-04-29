<?php

class showPositionsShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->positionsForm = $structureManager->createElement('positions', 'show', $structureElement->id)) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('positions'));
            $renderer->assign('action', 'receivePositions');
        }
    }
}