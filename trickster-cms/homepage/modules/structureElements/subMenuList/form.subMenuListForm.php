<?php

class SubMenuListFormStructure extends ElementForm
{
    protected $formClass = 'submenulist_form_block';
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'displayHeadingAutomatically' => [
            'type' => 'input.checkbox',
        ],
        'type' => [
            'type' => 'select.index',
            'options' => [
                'auto' => 'display_automatically',
                'select' => 'display_manual',
            ],
            'class' => 'submenulist_form_type',
        ],
        'menus' => [
            'type' => 'select.universal_options_multiple',
            'property' => 'menusList',
            'class' => 'submenulist_form_menus',
        ],
        'levels' => [
            'type' => 'select.array',
            'property' => 'levelsList',
        ],
        'maxLevels' => [
            'type' => 'select.array',
            'property' => 'maxLevelsList',
        ],
        'skipLevels' => [
            'type' => 'input.text',
        ],
        'popup' => [
            'type' => 'input.checkbox',
        ],
        'displayMenus' => [
            'type' => 'select.universal_options_multiple',
            'method' => 'getDisplayMenusInfo',
            'condition' => 'checkDisplayMenus',
            'translationGroup' => 'shared',
        ],
    ];

}
