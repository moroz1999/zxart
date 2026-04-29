<?php

class userDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'userId' => 'id',
            'searchTitle' => 'getTitle',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'userName' => 'userName',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email',
            'phone' => 'phone',
            'website' => 'website',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'dateCreated',
                'dateModified',
                'url',
                'userName',
                'firstName',
                'lastName',
                'email',
                'phone',
                'website',
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
            ],
        ];
    }
}