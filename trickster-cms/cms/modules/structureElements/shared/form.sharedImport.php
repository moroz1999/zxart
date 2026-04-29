<?php

class SharedImportStructure extends ElementForm
{
    protected $formClass = 'importform_form';
    protected $preset = 'importform_form_records';
    protected $structure = [
        'import' => [
            'type' => 'shared.importform',
        ],
    ];

}