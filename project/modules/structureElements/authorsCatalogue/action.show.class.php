<?php

class showAuthorsCatalogue extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('show');
        if (($firstParent = $structureElement->getFirstParentElement()) && $firstParent->requested) {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
        }
    }
}