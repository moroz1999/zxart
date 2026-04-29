<?php

class showFullListUsers extends structureElementAction
{
    /**
     * @param usersElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $structureManager->getElementsChildren($structureElement->id, 'content');

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'users.list.tpl');
            }
        }
    }
}