<?php

class receiveAuthorsCatalogue extends structureElementAction
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
            if ($firstParent = $structureManager->getElementsFirstParent($structureElement->getId())) {
                if ($lettersElement = $structureManager->getElementByMarker('authors')) {
                    if ($lettersList = $structureManager->getElementsChildren($lettersElement->getId())) {
                        $linksManager = $this->getService(linksManager::class);
                        $linksIndex = $linksManager->getElementsLinksIndex(
                            $firstParent->id,
                            'authorsCatalogue',
                            'parent'
                        );
                        foreach ($lettersList as $letter) {
                            if (!isset($linksIndex[$letter->id])) {
                                $linksManager->linkElements($firstParent->id, $letter->id, 'authorsCatalogue');
                            }
                            unset($linksIndex[$letter->id]);
                        }
                        foreach ($linksIndex as $link) {
                            $linksManager->unLinkElements(
                                $firstParent->id,
                                $link->childStructureId,
                                'authorsCatalogue'
                            );
                        }
                    }
                }
            }

            $controller->restart($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'items',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}

