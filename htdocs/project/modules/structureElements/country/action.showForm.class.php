<?php

class showFormCountry extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            if ($controller->getApplicationName() == 'admin') {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService('renderer');
                $renderer->assign('tabsTemplate', 'shared.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'country.form.tpl');
            } else {
                $structureElement->setViewName('form');
            }
        }
    }
}