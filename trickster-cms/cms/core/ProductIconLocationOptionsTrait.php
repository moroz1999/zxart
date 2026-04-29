<?php

trait ProductIconLocationOptionsTrait
{
    public $productIconLocationTypes = [
        'loc_top_left',
        'loc_top_right',
        'loc_bottom_left',
        'loc_bottom_right',
    ];

    /**
     * @return array
     */
    public function productIconLocationOptionsList()
    {
        return $this->productIconLocationTypes;
    }


}