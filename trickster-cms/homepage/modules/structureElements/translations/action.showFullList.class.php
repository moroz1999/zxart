<?php

class showFullListTranslations extends structureElementAction
{
    /**
     * @param translationsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate("shared.content.tpl");
            $renderer = $this->getService(renderer::class);
            if ($controller->getParameter("incomplete")) {
                $renderer->assign("contentSubTemplate", "translations.list_incomplete.tpl");
            } else {
                $renderer->assign("contentSubTemplate", "translations.list.tpl");
            }
        }
    }
}

