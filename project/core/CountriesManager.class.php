<?php

class CountriesManager extends errorLogger
{
    use ImportIdOperatorTrait;

    /**
     * @var linksManager
     */
    protected $linksManager;

    /**
     * @param linksManager $linksManager
     */
    public function setLinksManager($linksManager)
    {
        $this->linksManager = $linksManager;
    }

    public function importCountry($countryInfo, $origin)
    {
        /**
         * @var countryElement $element
         */
        if (!($element = $this->getElementByImportId($countryInfo['id'], $origin, 'country'))) {
            if ($element = $this->getLocationByName($countryInfo['title'])) {
                $this->saveImportId($element->id, $countryInfo['id'], $origin, 'country');
                $this->updateCountry($element, $countryInfo);
            } else {
                return $this->createCountry($countryInfo, $origin);
            }
        } else {
            $this->updateCountry($element, $countryInfo);
        }
        return $element;
    }

    public function getLocationByName($locationName)
    {
        $locationElement = false;
        $structureManager = $this->structureManager;

        if ($record = $this->db->table('module_country')
            ->select('id')
            ->where('title', 'like', $locationName)
            ->limit(1)
            ->first()
        ) {
            /**
             * @var countryElement $locationElement
             */
            $locationElement = $structureManager->getElementById($record['id']);
        }
        if ($record = $this->db->table('module_city')
            ->select('id')
            ->where('title', 'like', $locationName)
            ->limit(1)
            ->first()
        ) {
            /**
             * @var cityElement $locationElement
             */
            $locationElement = $structureManager->getElementById($record['id']);
        }

        return $locationElement;
    }


    /**
     * @param array $countryInfo
     * @param $origin
     * @return bool|countryElement
     */
    protected function createCountry($countryInfo, $origin)
    {
        $element = false;
        if ($countriesElement = $this->structureManager->getElementByMarker('countries')) {
            if ($element = $this->structureManager->createElement('country', 'show', $countriesElement->id)) {
                /**
                 * @var countryElement $element
                 */
                $this->updateCountry($element, $countryInfo);
                $this->saveImportId($element->id, $countryInfo['id'], $origin, 'country');
            }
        }
        return $element;
    }

    /**
     * @param countryElement $element
     * @param $countryInfo
     */
    protected function updateCountry($element, $countryInfo)
    {
        if (!$element->title) {
            $element->title = $countryInfo['title'];
        }
        $element->structureName = $element->title;
        $element->persistElementData();
    }

    public function joinCountries($mainCountryId, $joinedCountryId)
    {
        if ($joinedCountryId == $mainCountryId) {
            return false;
        }
        if ($joinedCountry = $this->structureManager->getElementById($joinedCountryId)) {
            if ($links = $this->linksManager->getElementsLinks($joinedCountryId, null, 'parent')) {
                foreach ($links as $link) {
                    $this->linksManager->unLinkElements($joinedCountryId, $link->childStructureId, $link->type);
                    $this->linksManager->linkElements($mainCountryId, $link->childStructureId, $link->type);
                }
            }

            $this->db->table('module_author')->where('city', '=', $joinedCountryId)->update(['city' => $mainCountryId]);
            $this->db->table('module_group')->where('city', '=', $joinedCountryId)->update(['city' => $mainCountryId]);
            $joinedCountry->deleteElementData();
        }

        return true;
    }
}