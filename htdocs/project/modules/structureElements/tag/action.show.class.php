<?php

class showTag extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($structureElement->final) {
            $sectionsLogics = $this->getService('SectionLogics');;
            if (($type = $sectionsLogics->getArtItemsType())) {
                $structureElement->setViewName($type);
            }
        } else {
            $structureElement->setViewName('short');
        }
    }
}

