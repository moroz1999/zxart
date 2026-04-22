<?php

class receiveCountry extends structureElementAction
{
    /**
     * @param countryElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $countriesListElements = $structureManager->getElementsByType('countriesList');

            $linksManager = $this->getService(linksManager::class);
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
                $countriesManager = $this->getService(CountriesManager::class);
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