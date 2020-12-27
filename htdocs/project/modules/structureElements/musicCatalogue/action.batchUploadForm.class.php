<?php

class batchUploadFormMusicCatalogue extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', 'musicCatalogue.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'musicCatalogue.form.tpl');
        }
    }
}


