<?php

class batchUploadFormPicturesCatalogue extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', 'picturesCatalogue.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'picturesCatalogue.form.tpl');
        }
    }
}


