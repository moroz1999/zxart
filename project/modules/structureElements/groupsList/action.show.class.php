<?php

class showGroupsList extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupsListElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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