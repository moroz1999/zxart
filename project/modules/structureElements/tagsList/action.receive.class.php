<?php

class receiveTagsList extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();

            if ($tagsElement = $structureManager->getElementByMarker('tags')) {
                if ($tagsList = $structureManager->getElementsChildren($tagsElement->id)) {
                    $linksManager = $this->getService('linksManager');
                    $linksIndex = $linksManager->getElementsLinksIndex($structureElement->id, 'tagsList', 'parent');
                    foreach ($tagsList as $tag) {
                        if (!isset($linksIndex[$tag->id])) {
                            $linksManager->linkElements($structureElement->id, $tag->id, 'tagsList');
                        }
                        unset($linksIndex[$tag->id]);
                    }
                    foreach ($linksIndex as $link) {
                        $linksManager->unLinkElements($structureElement->id, $link->childStructureId, 'tagsList');
                    }
                }
            }

            $controller->restart($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = ['title'];
    }

    public function setValidators(&$validators): void
    {
    }
}

