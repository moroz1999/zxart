<?php

class UserGroupFormStructure extends ElementForm
{
    protected $structure = [
        'groupName' => [
            'type' => 'input.text',
        ],
        'description' => [
            'type' => 'input.text',
        ],
        'marker' => [
            'type' => 'input.text',
        ],
    ];

}