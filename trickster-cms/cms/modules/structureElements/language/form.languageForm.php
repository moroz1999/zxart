<?php

class LanguageFormStructure extends ElementForm
{
    protected $structure = [
        'iso6391' => [
            'type' => 'input.text',
        ],
        'iso6393' => [
            'type' => 'input.text',
        ],
        'title' => [
            'type' => 'input.text',
        ],
        'hidden' => [
            'type' => 'input.checkbox',
        ],
        'image' => [
            'type' => 'input.image',
        ],
        'logoImage' => [
            'type' => 'input.image',
        ],
        'backgroundImage' => [
            'type' => 'input.image',
        ],
    ];
}