<?php

class SimpleSettingFormStructure extends ElementForm
{
    protected $structure = [
        'structureName' => [
            'type' => 'input.text',
        ],
        'value' => [
            'type' => 'input.text',
        ],
    ];

}