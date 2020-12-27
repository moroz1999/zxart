<?php

class receiveGroupsCatalogue extends structureElementAction
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
                if ($lettersElement = $structureManager->getElementByMarker('groups')) {
                    if ($lettersList = $structureManager->getElementsChildren($lettersElement->id)) {
                        $linksManager = $this->getService('linksManager');
                        $linksIndex = $linksManager->getElementsLinksIndex(
                            $firstParent->id,
                            'groupsCatalogue',
                            'parent'
                        );
                        foreach ($lettersList as $letter) {
                            if (!isset($linksIndex[$letter->id])) {
                                $linksManager->linkElements($firstParent->id, $letter->id, 'groupsCatalogue');
                            }
                            unset($linksIndex[$letter->id]);
                        }
                        foreach ($linksIndex as $link) {
                            $linksManager->unLinkElements(
                                $firstParent->id,
                                $link->childStructureId,
                                'groupsCatalogue'
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

