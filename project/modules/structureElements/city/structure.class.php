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

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['joinCity'] = 'text';
        $moduleStructure['latitude'] = 'floatNumber';
        $moduleStructure['longitude'] = 'floatNumber';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
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

    public function getMapMarkerData()
    {
        $data = [];
        $data['id'] = $this->id;
        $data['title'] = $this->getTitle();
        $data['amount'] = $this->getAmountInLocation();
        $data['latitude'] = $this->latitude;
        $data['longitude'] = $this->longitude;
        $data['url'] = $this->getUrl();

        return $data;
    }
}