<?php

class showFullListZxProd extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', 'zxProd.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'shared.contentlist.tpl');
        }
    }
}


