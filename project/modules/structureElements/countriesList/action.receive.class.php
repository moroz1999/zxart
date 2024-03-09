<?php

class receiveCountriesList extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $countriesElement = $structureManager->getElementByMarker('countries');
            $countriesList = $structureManager->getElementsChildren($countriesElement->id);

            $linksManager = $this->getService('linksManager');
            $compiledLinks = $linksManager->getElementsLinksIndex($structureElement->id, 'countries', 'parent');

            foreach ($countriesList as $country) {
                if (!isset($compiledLinks[$country->id])) {
                    $linksManager->linkElements($structureElement->id, $country->id, 'countries');
                } else {
                    unset($compiledLinks[$country->id]);
                }
            }
            foreach ($compiledLinks as $link) {
                $link->delete();
            }
            $controller->redirect($structureElement->URL);
        }

        $structureElement->executeAction("showForm");
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


