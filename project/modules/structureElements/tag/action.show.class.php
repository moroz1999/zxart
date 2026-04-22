<?php

class showTag extends structureElementAction
{
    /**
     * @param tagElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $sectionsLogics = $this->getService(SectionLogics::class);
        if (($type = $sectionsLogics->getArtItemsType())) {
            $structureElement->setViewName($type);
        }
    }
}

