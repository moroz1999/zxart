<?php

class batchUploadFormPicturesCatalogue extends structureElementAction
{
    /**
     * @param picturesCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', 'picturesCatalogue.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'picturesCatalogue.form.tpl');
        }
    }
}


