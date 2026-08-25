<?php

trait AutocompleteOptionsTrait
{
    public function getAutocompleteSelectOptions()
    {
        $values = [
            'userName',
            'email',
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