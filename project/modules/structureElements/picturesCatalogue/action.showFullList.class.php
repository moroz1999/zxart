<?php

class showFullListPicturesCatalogue extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->requested === true) {
            if ($structureElement->final === true) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService('renderer');
                $renderer->assign('tabsTemplate', 'picturesCatalogue.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'picturesCatalogue.list.tpl');
            }
        }
    }
}

