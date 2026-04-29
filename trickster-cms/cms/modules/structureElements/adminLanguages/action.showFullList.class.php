<?php

class showFullListAdminLanguages extends structureElementAction
{
    /**
     * @param adminLanguagesElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested) {
            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'shared.contentlist_singlepage.tpl');
            }
        }
    }
}