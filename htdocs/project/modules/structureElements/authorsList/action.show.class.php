<?php

class showAuthorsList extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $type = 'popular';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
        if ($structureElement->type == 'letters') {
            $renderer = $this->getService('renderer');
            $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
        }
    }
}