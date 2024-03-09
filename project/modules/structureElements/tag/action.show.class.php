<?php

class showTag extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $sectionsLogics = $this->getService('SectionLogics');;
        if (($type = $sectionsLogics->getArtItemsType())) {
            $structureElement->setViewName($type);
        }
    }
}

