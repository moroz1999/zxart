<?php

class showFormLetter extends structureElementAction
{
    /**
     * @param letterElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('tabsTemplate', false);
            $renderer->assign('contentSubTemplate', 'letter.form.tpl');
        }
    }
}


