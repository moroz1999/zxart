<?php

trait LocationProviderTrait
{
    use CacheOperatingElement;

    protected $cityElement;
    protected $countryElement;

    public function getCountryElement()
    {
        if ($this->countryElement === null) {
            $this->countryElement = false;

            $cache = $this->getElementsListCache('co', 60 * 60 * 24);
            if (($elements = $cache->load()) === false) {
                if ($countryId = $this->getCountryId()) {
                    /**
                     * @var structureManager $structureManager
                     */
                    $structureManager = $this->getService('structureManager');
                    $this->countryElement = $structureManager->getElementById($countryId);
                }
                $cache->save([$this->countryElement]);
            } else {
                $this->countryElement = reset($elements);
            }
        }
        return $this->countryElement;
    }

    public function getCityElement()
    {
        if ($this->cityElement === null) {
            $this->cityElement = false;

            $cache = $this->getElementsListCache('ci', 60 * 60 * 24);
            if (($elements = $cache->load()) === false) {
                if ($cityId = $this->getCityId()) {
                    /**
                     * @var structureManager $structureManager
                     */
                    $structureManager = $this->getService('structureManager');
                    $this->cityElement = $structureManager->getElementById($cityId);
                }
                $cache->save([$this->cityElement]);
            } else {
                $this->cityElement = reset($elements);
            }
        }
        return $this->cityElement;
    }

    public function getCityTitle()
    {
        if ($city = $this->getCityElement()) {
            return $city->title;
        }
        return "";
    }

    public function getCountryTitle()
    {
        if ($country = $this->getCountryElement()) {
            return $country->title;
        }
        return "";
    }

    abstract protected function getCityId();

    abstract protected function getCountryId();

    public function checkCountry(): bool
    {
        if ($city = $this->getCityElement()) {
            $country = $this->getCountryElement();
            $parentCountry = $city->getFirstParentElement();
            if ($parentCountry !== $country) {
                $this->countryElement = null;
                $this->country = $parentCountry->id;
                return false;
            }
        }
        return true;
    }
}

