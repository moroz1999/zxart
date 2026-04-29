<?php

class RegistrationInputFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.multi_language_text',
        ],
        'required' => [
            'type' => 'input.checkbox',
        ],
        'autocomplete' => [
            'type' => 'select.index',
            'method' => 'getAutocompleteSelectOptions',
            'translationGroup' => 'formfield',
            'defaultRequired' => true,
        ],
        'registrationForms' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getUserDataForms',

        ],
    ];

}