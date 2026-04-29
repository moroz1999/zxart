<?php

class showFullListSystem extends structureElementAction
{
    /**
     * @param systemElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureManager->getElementsChildren($structureElement->id, 'container');
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'shared.contentlist.tpl');
        }
    }
}