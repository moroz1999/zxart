<?php

trait ProductsAvailabilityOptionsTrait
{
    public $productsAvailabilityTypes = [
        'available',
        'quantity_dependent',
        'available_inquirable', // it is inquirable and buyable
        'inquirable', // it is inquirable only
        'unavailable',
    ];

    public function productsAvailabilityOptionsList()
    {
        return $this->productsAvailabilityTypes;
    }

    public function productsAvailabilityOptions($prefix = '')
    {
        $options = [];
        if (!empty($prefix)){
            foreach ($this->productsAvailabilityTypes as $typeKey => $typeValue) {
                $options[] = $prefix . $typeValue; // prefix if need
            }
        }
        return $options;
    }


    /**
     * @return array
     */
    public function getProductsAvailabilityOptions()
    {
        //  return $this->productsAvailabilityTypes;
        return $this->productsAvailabilityOptions('');
    }

    /**
     * @return array
     */
    public function getProductsAvailabilityOptionValues()
    {
        //  return $this->productsAvailabilityTypes;
        return $this->getProductsAvailabilityOptions();
    }

    public function getProductsAvailabilityOptionValuesList($currentValuesList)
    {
        $productsAvailabilityOptionValuesList = [];
        foreach ($currentValuesList as  $currentKey=>$currentValue) {
        //    var_dump($currentValue);
        //    var_dump($this->getProductsAvailabilityOptionValues[$currentValue]);
            $productsAvailabilityOptionValuesList[] = $this->productsAvailabilityTypes[$currentValue];
        }
        //  return $this->productsAvailabilityTypes;
        return $productsAvailabilityOptionValuesList;
    }


}