<?php

class countriesListElement extends metadataProvider
{
    use Location;

    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    /**
     * @var countryElement[]
     */
    protected $countriesList;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getCountriesList()
    {
        if ($this->countriesList === null) {
            $structureManager = $this->getService('structureManager');
            $this->countriesList = $structureManager->getElementsChildren($this->getId(), 'content', 'countries');
            $sort = [];
            foreach ($this->countriesList as $key => $countryElement) {
                if ($countryElement->getAmountInLocation()) {
                    $sort[] = $countryElement->getTitle();
                } else {
                    unset($this->countriesList[$key]);
                }
            }
            array_multisort($sort, SORT_ASC, $this->countriesList);
        }
        return $this->countriesList;
    }

    /**
     * @return false|string
     */
    public function getMapData(): string|false
    {
        $data = [];
        $data['markers'] = [];
        $data['startLatitude'] = 0;
        $data['startLongitude'] = 0;
        $data['zoom'] = 1;
        if ($countriesList = $this->getCountriesList()) {
            foreach ($countriesList as $country) {
                $marker = $country->getMapMarkerData();
                if ($marker['amount']) {
                    $data['markers'][] = $marker;
                }
            }
        }
        return json_encode($data);
    }

    /**
     * @return true
     */
    public function getAmountInLocation($type): bool
    {
        return true;
    }
}