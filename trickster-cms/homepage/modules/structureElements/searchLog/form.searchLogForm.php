<?php

class SearchLogFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.multi_language_text',
        ],
        'marker' => [
            'type' => 'input.text',
        ],
    ];

}