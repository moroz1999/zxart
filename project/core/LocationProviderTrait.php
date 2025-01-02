<?php

trait LocationProviderTrait
{
    use CacheOperatingElement;

    protected ?countryElement $countryElement;
    protected ?cityElement $cityElement;

    public function getCountryElement(): ?countryElement
    {
        if (isset($this->countryElement)) {
            return $this->countryElement;
        }

        $this->countryElement = null;
        $cache = $this->getElementsListCache('co', 86400);
        $elements = $cache->load();

        if ($elements !== null) {
            $this->countryElement = $elements[0] ?? null;
            return $this->countryElement;
        }

        $countryId = $this->getCountryId();
        if (!$countryId) {
            $cache->save([]);
            return null;
        }

        /** @var structureManager $structureManager */
        $structureManager = $this->getService('structureManager');
        $element = $structureManager->getElementById($countryId);

        if ($element instanceof countryElement) {
            $this->countryElement = $element;
            $cache->save([$element]);
        } else {
            $cache->save([]);
        }

        return $this->countryElement;
    }

    public function getCityElement(): ?cityElement
    {
        if (isset($this->cityElement)) {
            return $this->cityElement;
        }

        $this->cityElement = null;
        $cache = $this->getElementsListCache('ci', 86400);
        $elements = $cache->load();

        if ($elements !== null) {
            $this->cityElement = $elements[0] ?? null;
            return $this->cityElement;
        }

        $cityId = $this->getCityId();
        if (!$cityId) {
            $cache->save([]);
            return null;
        }

        /** @var structureManager $structureManager */
        $structureManager = $this->getService('structureManager');
        $element = $structureManager->getElementById($cityId);

        if ($element instanceof cityElement) {
            $this->cityElement = $element;
            $cache->save([$element]);
        } else {
            $cache->save([]);
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

