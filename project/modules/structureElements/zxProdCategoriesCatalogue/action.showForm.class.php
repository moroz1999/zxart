<?php

class showFormZxProdCategoriesCatalogue extends structureElementAction
{
    /**
     * @param zxProdCategoriesCatalogueElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'zxProdCategoriesCatalogue.form.tpl');
        }
    }
}


