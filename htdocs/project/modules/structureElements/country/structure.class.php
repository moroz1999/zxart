<?php

/**
 * Class countryElement
 *
 * @property string $title
 * @property int $joinCountry
 * @property float $latitude
 * @property float $longitude
 */
class countryElement extends structureElement
{
    use SortedChildrenListTrait;
    use ImportedItemTrait;
    use CacheOperatingElement;
    use Location;

    public $dataResourceName = 'module_country';
    public $allowedTypes = ['city'];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $locationPropertyName = 'country';
    /**
     * @var cityElement[]
     */
    protected $citiesList;
    /**
     * @var authorElement[]
     */
    protected $authorsList;

    public function getCitiesList()
    {
        if ($this->citiesList === null) {
            $structureManager = $this->getService('structureManager');
            $this->citiesList = $structureManager->getElementsChildren($this->id, 'content');
            $sort = [];
            foreach ($this->citiesList as $key => $cityElement) {
                if ($cityElement->getAmountInLocation()) {
                    $sort[] = $cityElement->getTitle();
                } else {
                    unset($this->citiesList[$key]);
                }
            }
            array_multisort($sort, SORT_ASC, $this->citiesList);
        }
        return $this->citiesList;
    }

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['joinCountry'] = 'text';
        $moduleStructure['latitude'] = 'floatNumber';
        $moduleStructure['longitude'] = 'floatNumber';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getMapData()
    {
        $data = [];
        $data['markers'] = [];
        $data['startLatitude'] = $this->latitude;
        $data['startLongitude'] = $this->longitude;
        $data['zoom'] = 5;

        foreach ($this->getCitiesList() as $cityElement) {
            $marker = $cityElement->getMapMarkerData();
            if ($marker['amount']) {
                $data['markers'][] = $marker;
            }
        }
        return json_encode($data);
    }

    public function getMapMarkerData()
    {
        $data = [];
        $data['title'] = $this->getTitle();
        $data['amount'] = $this->getAmountInLocation();
        $data['latitude'] = $this->latitude;
        $data['longitude'] = $this->longitude;
        $data['url'] = $this->getUrl();
        return $data;
    }
}


