<?php

class showFormZxProdCategoriesCatalogue extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'zxProdCategoriesCatalogue.form.tpl');
        }
    }
}


