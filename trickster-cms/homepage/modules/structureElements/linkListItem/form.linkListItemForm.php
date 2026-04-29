<?php

class LinkListItemFormStructure extends ElementForm
{
    protected $formData;
    protected $formClass = 'linklistitem_form';
    protected $structure = [
        'title' => [
            'type' => 'input.text',
            'textClass' => 'linklistitem_form_search_title',
        ],
        'image' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'imageClass' => 'linklistitem_form_search_image',
        ],
        'secondaryImage' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'imageClass' => 'linklistitem_form_search_image',
        ],
        'tertiaryImage' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'imageClass' => 'linklistitem_form_search_image',
        ],
        'quaternaryImage' => [
            'type' => 'input.image',
            'preset' => 'adminImage',
            'imageClass' => 'linklistitem_form_search_image',
        ],
        'content' => [
            'type' => 'input.html',
            'class' => 'linklistitem_form_search_content',
        ],
        'linkText' => [
            'type' => 'input.text',
        ],
        'link' => [
            'type' => 'input.text',
            'textClass' => 'linklistitem_form_search_url',
        ],
    ];


    protected function getSearchTypes()
    {
        return $this->element->getSearchTypesString('admin');
    }

    public function getFormComponents()
    {
        $structure = [];
        $structure['fixedId'] = [
            'type' => 'ajaxsearch',
            'class' => 'linklistitem_form_search',
            'property' => 'connectedMenu',
            'types' => $this->getSearchTypes(),
        ];
        return $structure + $this->structure;
    }
}