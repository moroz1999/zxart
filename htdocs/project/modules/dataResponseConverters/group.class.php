<?php

class groupDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'searchTitle' => 'getSearchTitle',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'city' => 'getCityTitle',
            'country' => 'getCountryTitle',
            'subGroupIds' => 'getSubGroupIds',
            'importIds' => 'getImportIdsIndex',
            'aliases' => 'getAliasElementsIds',
            'prods' => 'getProdsInfo',
            'prodsAmount' => 'getProdsAmount',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'url',
                'dateCreated',
                'dateModified',
                'city',
                'country',
                'subGroupIds',
                'importIds',
                'aliases',
            ],
            'apiShort' => [
                'id',
                'title',
                'dateModified',
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
            ],
            'zxProdsList' => [
                'id',
                'title',
                'url',
                'prodsAmount',
                'prods',
            ],
        ];
    }
}