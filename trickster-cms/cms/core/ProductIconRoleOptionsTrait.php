<?php

trait ProductIconRoleOptionsTrait
{
    /**
     * @var array
     */
    protected $productIconRoleTypes = [
        'role_simple',
        'role_date',
        'role_general_discount',
        'role_availability',
        'role_by_parameter',
    ];

    /**
     * @return array
     */
    public function productIconRoleOptionsList()
    {
        return $this->productIconRoleTypes;
    }

    public function getProductIconRoleType($value)
    {
        if (isset($this->productIconRoleTypes[$value])) {
            return $this->productIconRoleTypes[$value];
        }
        return 'role_simple';
    }

}