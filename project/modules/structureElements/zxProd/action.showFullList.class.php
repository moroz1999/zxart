<?php

class showFullListZxProd extends structureElementAction
{
    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', 'zxProd.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'shared.contentlist.tpl');
        }
    }
}


