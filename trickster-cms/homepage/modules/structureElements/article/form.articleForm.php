<?php

class ArticleFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'hideTitle' => [
            'type' => 'input.checkbox'
        ],
        'content' => [
            'type' => 'input.html',
        ],
        'image' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'filename' => 'image',
        ],
        'displayMenus' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getDisplayMenusInfo',
            'condition' => 'checkDisplayMenus',
            'translationGroup' => 'shared',
        ],
    ];
    protected $additionalContent = 'shared.contentlist.tpl';
}