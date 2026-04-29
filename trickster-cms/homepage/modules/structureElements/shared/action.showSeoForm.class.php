<?php

class showSeoFormShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            if(!empty($structureElement->getMultiLanguageFields())) {
                $renderer->assign('form', $structureElement->getForm('multiLanguageSeo'));
            } else {
                $renderer->assign('form', $structureElement->getForm('singleLanguageSeo'));
            }
            $renderer->assign('action', 'receiveSeo');
        }
    }
}