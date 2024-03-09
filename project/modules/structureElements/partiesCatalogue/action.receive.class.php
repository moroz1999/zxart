<?php

class receivePartiesCatalogue extends structureElementAction
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
                if ($yearsElement = $structureManager->getElementByMarker('parties')) {
                    if ($yearsInfo = $structureManager->getElementsChildren($yearsElement->id)) {
                        $linksManager = $this->getService('linksManager');
                        $linksIndex = $linksManager->getElementsLinksIndex(
                            $firstParent->id,
                            'partiesCatalogue',
                            'parent'
                        );
                        foreach ($yearsInfo as $year) {
                            if (!isset($linksIndex[$year->id])) {
                                $linksManager->linkElements($firstParent->id, $year->id, 'partiesCatalogue');
                            }
                            unset($linksIndex[$year->id]);
                        }
                        foreach ($linksIndex as $link) {
                            $linksManager->unLinkElements(
                                $firstParent->id,
                                $link->childStructureId,
                                'partiesCatalogue'
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

