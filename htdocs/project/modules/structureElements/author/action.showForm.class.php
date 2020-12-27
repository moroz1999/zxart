<?php

class showFormAuthor extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            if ($controller->getApplicationName() == 'admin') {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService('renderer');
                $renderer->assign('tabsTemplate', 'author.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'author.form.tpl');
            } else {
                $structureElement->setViewName('form');
            }
        }
    }
}


