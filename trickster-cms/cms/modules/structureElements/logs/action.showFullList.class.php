<?php

class showFullListLogs extends structureElementAction
{
    /**
     * @param logsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'shared.contentlist.tpl');
        }
    }
}