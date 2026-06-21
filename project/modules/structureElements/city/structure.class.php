<?php

/**
 * Class cityElement
 */
class cityElement extends structureElement
{
    use CacheOperatingElement;
    use Location;

    public $dataResourceName = 'module_city';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $locationPropertyName = 'city';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['joinCity'] = 'text';
        $moduleStructure['latitude'] = 'floatNumber';
        $moduleStructure['longitude'] = 'floatNumber';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
    }

    public function getUrl($action = null)
    {
        if ($action === null && ($sectionUrl = $this->getGeoSectionUrl()) !== null) {
            return $sectionUrl . '?city=' . $this->getId();
        }
        return parent::getUrl($action);
    }

    private function getGeoSectionUrl(): ?string
    {
        $element = $this->getFirstParentElement();
        while ($element !== null) {
            if ($element->structureType === 'countriesList') {
                return $element->getUrl();
            }
            $element = $element->getFirstParentElement();
        }
        return null;
    }

    public function getCountryId()
    {
        if ($firstParent = $this->getFirstParentElement()) {
            if ($firstParent->structureType == 'country') {
                return $firstParent->id;
            }
        }
        return false;
    }

    /**
     * @return (int|mixed)[]
     *
     * @psalm-return array{id: int, title: mixed, amount: mixed, latitude: mixed, longitude: mixed, url: mixed}
     */
    public function getMapMarkerData(): array
    {
        $data = [];
        $data['id'] = $this->getId();
        $data['title'] = $this->getTitle();
        $data['amount'] = $this->getAmountInLocation();
        $data['latitude'] = $this->latitude;
        $data['longitude'] = $this->longitude;
        $data['url'] = $this->getUrl();

        return $data;
    }
}