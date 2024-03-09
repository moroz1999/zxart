<?php

class ZxProdCategorySeoStructure extends ElementForm
{
    protected $structure = [
        'structureName' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaTitle' => [
            'type' => 'input.multi_language_text',
        ],
        'h1' => [
            'type' => 'input.multi_language_text',
        ],
        'metaDescription' => [
            'type' => 'input.multi_language_textarea',
        ],
        'canonicalUrl' => [
            'type' => 'input.text',
            'translationGroup' => 'seo',
        ],
        'metaDenyIndex' => [
            'type' => 'input.checkbox',
        ],
        'prods_seo' => [
            'type' => 'show.heading',
        ],
        'metaTitleTemplate' => [
            'type' => 'input.multi_language_text',
        ],
        'metaH1Template' => [
            'type' => 'input.multi_language_text',
        ],
        'metaDescriptionTemplate' => [
            'type' => 'input.multi_language_textarea',
        ],
    ];


    /**
     * @return string
     *
     * @psalm-return 'seo'
     */
    public function getTranslationGroup()
    {
        return 'seo';
    }
}