<?php

class RegistrationFormStructure extends ElementForm
{
    protected $formClass = 'registration_form';
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'type' => [
            'type' => 'select.array',
            'options' => ['registration', 'userdata'],
            'translationGroup' => 'registration',
        ],
        'content' => [
            'type' => 'input.html',
        ],
        'registrationFieldsIds' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getRegistrationFields',

        ],
        'registrationGroupsIds' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getUserGroupsOptions',

        ],
    ];

}