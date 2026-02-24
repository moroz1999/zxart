<?php

class showAuthorsList extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $type = 'popular';
        if ($structureElement->type) {
            $type = $structureElement->type;
        }
        $structureElement->setViewName($type);
        if ($structureElement->type == 'letters') {
            $renderer = $this->getService(renderer::class);
            $renderer->assign('lettersInfo', $structureElement->getLettersSelectorInfo());
        }
    }
}