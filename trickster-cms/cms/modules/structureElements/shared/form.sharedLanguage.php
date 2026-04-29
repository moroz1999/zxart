<?php

class SharedLanguageStructure extends ElementForm
{
    protected $formClass = 'languages_form';
    protected $structure = [
        'formRelativesInput' => [
            'type' => 'input.menus_connection',
            'class' => 'languages_form_searchinput',
        ],
    ];

}