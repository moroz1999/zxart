<?php

class FileFormStructure extends ElementForm
{
    protected $structure = [
        'title' => [
            'type' => 'input.text',
        ],
        'file' => [
            'type' => 'input.file',
            'fileNameProperty' => 'fileName',
        ],
        'image' => [
            'type' => 'input.image',
            'fileNameProperty' => 'imageFileName',
        ],
    ];

}