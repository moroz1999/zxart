<?php

class SharedMultiLanguageSeoStructure extends ElementForm
{
    protected $structure = [
        'structureName' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaTitle' => [
            'type' => 'input.multi_language_text',
            'translationGroup' => 'seo',
        ],
        'h1' => [
            'type' => 'input.multi_language_text',
            'translationGroup' => 'seo',
        ],
        'metaDescription' => [
            'type' => 'input.multi_language_textarea',
            'translationGroup' => 'seo',
        ],
        'canonicalUrl' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaDenyIndex' => [
            'type' => 'input.checkbox',
            'translationGroup' => 'seo',
        ],
    ];


    public function getTranslationGroup()
    {
        return 'seo';
    }
}