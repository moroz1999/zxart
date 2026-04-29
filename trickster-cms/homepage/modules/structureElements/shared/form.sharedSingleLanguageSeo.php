<?php

class SharedSingleLanguageSeoStructure extends ElementForm
{
    protected $structure = [
        'structureName' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaTitle' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'h1' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaDescription' => [
            'type' => 'input.textarea',
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