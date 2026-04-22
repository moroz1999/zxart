<?php

class showFormAuthor extends structureElementAction
{
    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            if ($controller->getApplicationName() == 'admin') {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('tabsTemplate', 'author.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'author.form.tpl');
            } else {
                $structureElement->setViewName('form');
            }
        }
    }
}


