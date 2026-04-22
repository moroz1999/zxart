<?php

class showAuthorsList extends structureElementAction
{
    /**
     * @param authorsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $type = 'popular';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
        if ($structureElement->type == 'letters') {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
            $renderer->assign('currentLetter', $controller->getApplication()->getParameter('letter') ?? '');
            $renderer->assign('authorsListItems', $structureElement->items);
        }
    }
}