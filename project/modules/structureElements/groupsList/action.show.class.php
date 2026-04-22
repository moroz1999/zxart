<?php

class showGroupsList extends structureElementAction
{
    /**
     * @param groupsListElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->requested === true) {
            $type = 'latest';
            if ($structureElement->type) {
                $type = $structureElement->type;
            }
            $structureElement->setViewName($type);

            if ($structureElement->final === true && $structureElement->type === 'letters') {
                $renderer = $this->getService(renderer::class);
                $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
                $renderer->assign('currentLetter', $controller->getApplication()->getParameter('letter') ?? '');
            }
        }
    }
}