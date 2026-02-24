<?php

class showFormCountry extends structureElementAction
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
                $renderer->assign('tabsTemplate', 'shared.tabs.tpl');
                $renderer->assign('contentSubTemplate', 'country.form.tpl');
            } else {
                $structureElement->setViewName('form');
            }
        }
    }
}