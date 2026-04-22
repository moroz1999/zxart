<?php

class showFormParty extends structureElementAction
{
    /**
     * @param partyElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'party.form.tpl');
        }

        if ($firstParent = $structureManager->getElementsFirstParent($structureElement->getId())) {
            $structureElement->year = $firstParent->structureName;
        }
    }
}


