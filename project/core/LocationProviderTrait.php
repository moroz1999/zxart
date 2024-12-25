<?php

trait LocationProviderTrait
{
    use CacheOperatingElement;

    protected ?cityElement $cityElement;
    protected ?countryElement $countryElement;

    public function getCountryElement(): ?countryElement
    {
        if (!isset($this->countryElement)) {
            $this->countryElement = null;

            $cache = $this->getElementsListCache('co', 60 * 60 * 24);
            if (($elements = $cache->load()) === null) {
                if ($countryId = $this->getCountryId()) {
                    /**
                     * @var structureManager $structureManager
                     */
                    $structureManager = $this->getService('structureManager');
                    $this->countryElement = $structureManager->getElementById($countryId);
                }
                $cache->save([$this->countryElement]);
            } else {
                $this->countryElement = $elements[0] ?? null;;
            }
        }
        return $this->countryElement;
    }

    public function getCityElement(): ?cityElement
    {
        if (!isset($this->cityElement)) {
            $this->cityElement = null;

            $cache = $this->getElementsListCache('ci', 60 * 60 * 24);
            if (($elements = $cache->load()) === null) {
                if ($cityId = $this->getCityId()) {
                    /**
                     * @var structureManager $structureManager
                     */
                    $structureManager = $this->getService('structureManager');
                    $this->cityElement = $structureManager->getElementById($cityId);
                }
                $cache->save([$this->cityElement]);
            } else {
                $this->cityElement = $elements[0] ?? null;;
            }
        }
        return $this->cityElement;
    }

    public function getCityTitle(): ?string
    {
        if ($city = $this->getCityElement()) {
            return $city->title;
        }
        return null;
    }

    public function getCountryTitle(): ?string
    {
        if ($country = $this->getCountryElement()) {
            return $country->title;
        }
        return null;
    }

    abstract protected function getCityId();

    abstract protected function getCountryId();

    public function checkCountry(): bool
    {
        if ($city = $this->getCityElement()) {
            $country = $this->getCountryElement();
            $parentCountry = $city->getFirstParentElement();
            if ($parentCountry !== null && $parentCountry !== $country) {
                $this->countryElement = null;
                $this->country = $parentCountry->id;
                return false;
            }
        }
        return true;
    }

    public function matchesCountry(string $country): bool
    {
        $countryElement = $this->getCountryElement();
        if ($countryElement === null) {
            return false;
        }
        return $countryElement->matchesTitle($country);
    }

    public function matchesCity(string $city): bool
    {
        $cityElement = $this->getCityElement();
        if ($cityElement === null) {
            return false;
        }
        return $cityElement->matchesTitle($city);
    }
}

