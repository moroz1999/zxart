<?php

class showFormPartiesCatalogue extends structureElementAction
{
    /**
     * @param partiesCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'partiesCatalogue.form.tpl');
        }
    }
}


