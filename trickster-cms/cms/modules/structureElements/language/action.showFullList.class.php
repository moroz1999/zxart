<?php

class showFullListLanguage extends structureElementAction
{
    /**
     * @param languageElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested === true) {
            $contentType = 'structure';
            if ($controller->getApplicationName() != 'adminAjax') {
                if ($controller->getParameter('view')) {
                    $contentType = $controller->getParameter('view');
                }
            }
            $structureManager->setNewElementLinkType($contentType);
            $structureManager->getElementsChildren($structureElement->id);

            if ($structureElement->final) {
                $structureElement->setTemplate('shared.content.tpl');
                $renderer = $this->getService(renderer::class);
                $renderer->assign('contentSubTemplate', 'shared.contentlist.tpl');
                $renderer->assign('contentType', $contentType);
            }
        }
    }
}