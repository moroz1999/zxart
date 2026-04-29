<?php

class showTextsShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('action', 'receiveTexts');
            if(method_exists($structureElement, 'getSubArticles')) {
                $renderer->assign('contentList', $structureElement->getSubArticles());
            }
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('texts'));
        }
    }
}