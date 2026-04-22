<?php

class showSeoFormZxProdCategory extends structureElementAction
{
    /**
     * @param zxProdCategoryElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('seo'));
            $renderer->assign('action', 'receiveSeo');
        }
    }
}