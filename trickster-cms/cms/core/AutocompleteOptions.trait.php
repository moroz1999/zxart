<?php

trait AutocompleteOptionsTrait
{
    public function getAutocompleteSelectOptions()
    {
        $values = [
            'company',
            'userName',
            'fullName',
            'firstName',
            'lastName',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'postIndex',
            'dpdRegion',
            'dpdPoint',
            'post24Region',
            'post24Automate',
            'smartPostRegion',
            'smartPostAutomate',
            'product',
            'vatNumber',
        ];
        $options = [];
        foreach ($values as $value) {
            $options[$value] = 'autocomplete_' . $value;
        }
        return $options;
    }
}