<?php

class receiveCountry extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param countryElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $countriesListElements = $structureManager->getElementsByType('countriesList');

            $linksManager = $this->getService('linksManager');
            $compiledLinks = $linksManager->getElementsLinksIndex($structureElement->id, 'countries', 'child');

            foreach ($countriesListElements as $countriesListElement) {
                if (!isset($compiledLinks[$countriesListElement->id])) {
                    $linksManager->linkElements($countriesListElement->id, $structureElement->id, 'countries');
                } else {
                    unset($compiledLinks[$countriesListElement->id]);
                }
            }
            foreach ($compiledLinks as $link) {
                $link->delete();
            }

            if ($structureElement->joinCountry) {
                /**
                 * @var CountriesManager $countriesManager
                 */
                $countriesManager = $this->getService('CountriesManager');
                $countriesManager->joinCountries($structureElement->id, $structureElement->joinCountry);
            }
            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'joinCountry',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}