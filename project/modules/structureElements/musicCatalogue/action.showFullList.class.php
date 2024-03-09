<?php

class showFullListMusicCatalogue extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested === true) {
            if ($structureElement->final === true) {
                $structureElement->getChildrenList();

                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService('renderer');
                $renderer->assign('tabsTemplate', 'musicCatalogue.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'musicCatalogue.list.tpl');
            }
        }
    }
}

