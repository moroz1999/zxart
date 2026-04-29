<?php

class FormFieldsGroupFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
    ];

    protected $additionalContent = 'shared.contentlist.tpl';
}