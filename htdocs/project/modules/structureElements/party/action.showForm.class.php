<?php

class showFormParty extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService('renderer');
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'party.form.tpl');
        }

        if ($firstParent = $structureManager->getElementsFirstParent($structureElement->id)) {
            $structureElement->year = $firstParent->structureName;
        }
    }
}


