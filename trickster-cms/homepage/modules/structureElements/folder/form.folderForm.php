<?php

class FolderFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'hidden' => [
            'type' => 'input.checkbox',
        ],
        'columns' => [
            'type' => 'select.index',
            'options' => [
                'left' => 'columns_left',
                'right' => 'columns_right',
                'both' => 'columns_both',
                'none' => 'columns_none',
            ],
            'translationGroup' => 'selector',
        ],
        'image' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'filename' => 'originalName',
        ],
        'externalUrl' => [
            'type' => 'input.text',
            'text' => 'focused_input',
        ],
        'displayMenus' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getDisplayMenusInfo',
            'condition' => 'checkDisplayMenus',
            'translationGroup' => 'shared',
        ],
    ];

}