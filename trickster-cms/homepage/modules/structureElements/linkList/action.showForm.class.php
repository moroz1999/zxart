<?php

class showFormLinkList extends structureElementAction
{
    /**
     * @param linkListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            $structureManager->getElementsChildren($structureElement->id);
            if ($structureElement->fixedId) {
                $structureElement->connectedMenu = $structureManager->getElementById($structureElement->fixedId);
            }

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'component.form.tpl');
                $renderer->assign('form', $structureElement->getForm('form'));
            }
        }
    }
}