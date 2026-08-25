<?php

class UserFormStructure extends ElementForm
{
    protected $structure = [
        'userName' => [
            'type' => 'input.text',
        ],
        'password' => [
            'type' => 'input.password',
        ],
        'email' => [
            'type' => 'input.text',
        ],
        'subscribe' => [
            'type' => 'input.checkbox',
        ],
        'userGroups' => [
            'type' => 'select.universal_options_multiple',
            'property' => 'userGroupsList',

        ],
    ];

}