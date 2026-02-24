<?php

class showFormAuthor extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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


