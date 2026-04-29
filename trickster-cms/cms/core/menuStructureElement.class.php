<?php

abstract class menuStructureElement extends menuDependantStructureElement
{
    protected $subMenuList;

    public function getSubMenuList($linkType = 'structure')
    {
        if ($this->subMenuList == null) {
            $this->subMenuList = [];
            $structureManager = $this->getService('structureManager');
            if ($list = $structureManager->getElementsChildren($this->id, 'container')) {
                foreach ($list as $item) {
                    if (!$item->hidden) {
                        $this->subMenuList[] = $item;
                    }
                }
            }
        }
        return $this->subMenuList;
    }
}
