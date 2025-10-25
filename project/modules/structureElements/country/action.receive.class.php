<?php

class receiveCountry extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param countryElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $countriesListElements = $structureManager->getElementsByType('countriesList');

            $linksManager = $this->getService('linksManager');
            $compiledLinks = $linksManager->getElementsLinksIndex($structureElement->getId(), 'countries', 'child');

            foreach ($countriesListElements as $countriesListElement) {
                if (!isset($compiledLinks[$countriesListElement->getId()])) {
                    $linksManager->linkElements($countriesListElement->getId(), $structureElement->getId(), 'countries');
                } else {
                    unset($compiledLinks[$countriesListElement->getId()]);
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
                $countriesManager->joinCountries($structureElement->getId(), $structureElement->joinCountry);
            }
            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'joinCountry',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}