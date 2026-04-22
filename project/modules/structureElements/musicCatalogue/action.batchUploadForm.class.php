<?php

class batchUploadFormMusicCatalogue extends structureElementAction
{
    /**
     * @param musicCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', 'musicCatalogue.tabs.tpl');
            $renderer->assign('contentSubTemplate', 'musicCatalogue.form.tpl');
        }
    }
}


