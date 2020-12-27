<?php

class receiveAuthorsCatalogue extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();
            if ($firstParent = $structureManager->getElementsFirstParent($structureElement->id)) {
                if ($lettersElement = $structureManager->getElementByMarker('authors')) {
                    if ($lettersList = $structureManager->getElementsChildren($lettersElement->id)) {
                        $linksManager = $this->getService('linksManager');
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

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'items',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

