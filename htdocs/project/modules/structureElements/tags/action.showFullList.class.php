<?php

class showFullListTags extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', 'shared.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'tags.list.tpl');
        }
    }
}

