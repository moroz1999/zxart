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
                if ($tagsList = $structureManager->getElementsChildren($tagsElement->getId())) {
                    $linksManager = $this->getService(linksManager::class);
                    $linksIndex = $linksManager->getElementsLinksIndex($structureElement->getId(), 'tagsList', 'parent');
                    foreach ($tagsList as $tag) {
                        if (!isset($linksIndex[$tag->id])) {
                            $linksManager->linkElements($structureElement->getId(), $tag->id, 'tagsList');
                        }
                        unset($linksIndex[$tag->id]);
                    }
                    foreach ($linksIndex as $link) {
                        $linksManager->unLinkElements($structureElement->getId(), $link->childStructureId, 'tagsList');
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

