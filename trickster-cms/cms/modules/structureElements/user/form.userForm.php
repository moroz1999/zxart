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
        'company' => [
            'type' => 'input.text',
        ],
        'firstName' => [
            'type' => 'input.text',
        ],
        'lastName' => [
            'type' => 'input.text',
        ],
        'address' => [
            'type' => 'input.text',
        ],
        'city' => [
            'type' => 'input.text',
        ],
        'postIndex' => [
            'type' => 'input.text',
        ],
        'country' => [
            'type' => 'input.text',
        ],
        'email' => [
            'type' => 'input.text',
        ],
        'phone' => [
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