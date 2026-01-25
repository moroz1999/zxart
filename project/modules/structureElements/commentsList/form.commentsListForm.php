<?php

class CommentsListFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [

        ],
        'type' => [
            'type' => 'select.array',
            'options' => ['latest', 'popular', 'all'],
        ],
    ];

}