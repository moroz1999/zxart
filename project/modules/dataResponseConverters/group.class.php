<?php

class groupDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => function ($element) {
                return html_entity_decode($element->title, ENT_QUOTES);
            },
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
            'publishedProds' => 'getPublisherProdsInfo',
            'releases' => 'getReleasesInfo',
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
                'prods',
                'publishedProds',
                'releases',
            ],
        ];
    }
}