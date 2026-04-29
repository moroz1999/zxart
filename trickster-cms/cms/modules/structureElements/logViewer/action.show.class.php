<?php

class showLogViewer extends structureElementAction
{
    /**
     * @param logViewerElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'logViewer.content.tpl');
        }
    }
}