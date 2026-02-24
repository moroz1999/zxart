<?php

class showFormZxItemsList extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'zxItemsList.form.tpl');
        }

        if ($firstParent = $structureManager->getElementsFirstParent($structureElement->getId())) {
            $structureElement->year = $firstParent->structureName;
        }
    }
}


