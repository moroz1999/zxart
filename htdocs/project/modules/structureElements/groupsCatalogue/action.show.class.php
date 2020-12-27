<?php

class showGroupsCatalogue extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $structureElement->setViewName('show');
        if (($firstParent = $structureElement->getFirstParentElement()) && $firstParent->requested) {
            $renderer = $this->getService('renderer');
            $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
        }
    }
}

