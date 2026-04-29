<?php

class SharedFormStructure extends ElementForm
{
    protected $structure = [
        'title'        => [
            'type' => 'input.multi_language_text',
            'translationGroup' => 'shared',
        ],
        'marker'       => [
            'type' => 'input.text',
            'translationGroup' => 'shared',
        ],
        'displayMenus' => [
            'type'      => 'select.universal_options_multiple',
            'method'    => 'getDisplayMenusInfo',
            'condition' => 'checkDisplayMenus',
            'translationGroup' => 'shared',
        ]
    ];


    public function getTranslationGroup()
    {
        return 'shared';
    }
}