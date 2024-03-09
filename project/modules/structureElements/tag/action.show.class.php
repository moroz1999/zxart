<?php

class showTag extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $sectionsLogics = $this->getService('SectionLogics');;
        if (($type = $sectionsLogics->getArtItemsType())) {
            $structureElement->setViewName($type);
        }
    }
}

