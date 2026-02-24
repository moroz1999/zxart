<?php

class receiveZxProdCategoriesCatalogue extends structureElementAction
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
                if ($zxProdCategoriesElement = $structureManager->getElementByMarker('ZxProdCategories')) {
                    if ($categoriesList = $structureManager->getElementsChildren($zxProdCategoriesElement->getId())) {
                        $linksManager = $this->getService(linksManager::class);
                        $linksIndex = $linksManager->getElementsLinksIndex(
                            $firstParent->id,
                            'softCatalogue',
                            'parent'
                        );
                        foreach ($categoriesList as $letter) {
                            if (!isset($linksIndex[$letter->id])) {
                                $linksManager->linkElements($firstParent->id, $letter->id, 'softCatalogue');
                            }
                            unset($linksIndex[$letter->id]);
                        }
                        foreach ($linksIndex as $link) {
                            $linksManager->unLinkElements(
                                $firstParent->id,
                                $link->childStructureId,
                                'softCatalogue'
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
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}

