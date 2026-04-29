<?php

class TranslationFormStructure extends ElementForm
{
    protected $formClass = 'translation_form';
    protected $structure = [
        'structureName' => [
            'type' => 'input.text',
        ],
        'valueType' => [
            'type' => 'select.index',
            'options' => [
                'text' => 'valuetext',
                'textarea' => 'valuetextarea',
                'html' => 'valuehtml',
            ],
            'class' => 'translation_form_type',
            'translationGroup' => 'admintranslation',
        ],
        'valueText' => [
            'type' => 'input.multi_language_text',
            'style' => 'display:none;',
            'class' => 'translation_form_text_related',
        ],
        'valueTextarea' => [
            'type' => 'input.multi_language_textarea',
            'style' => 'display:none;',
            'class' => 'translation_form_textarea_related',
        ],
        'valueHtml' => [
            'type' => 'input.multi_language_content',
            'style' => 'display:none;',
            'class' => 'translation_form_html_related',
        ],
    ];

}