<?php

class authorDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', title: Closure(mixed):string, searchTitle: 'getSearchTitle', url: 'getUrl', structureType: 'structureType', dateCreated: Closure(mixed):mixed, dateModified: Closure(mixed):mixed, realName: 'realName', tunesQuantity: 'tunesQuantity', picturesQuantity: 'picturesQuantity', city: 'getCityTitle', country: 'getCountryTitle', importIds: 'getImportIdsIndex', aliases: 'getAliasElementsIds', prods: 'getProdsInfo', prodsAmount: 'getProdsAmount', publishedProds: 'getPublisherProdsInfo', releases: 'getReleasesInfo'}
     */
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
            'realName' => 'realName',
            'tunesQuantity' => 'tunesQuantity',
            'picturesQuantity' => 'picturesQuantity',
            'city' => 'getCityTitle',
            'country' => 'getCountryTitle',
            'importIds' => 'getImportIdsIndex',
            'aliases' => 'getAliasElementsIds',
            'prods' => 'getProdsInfo',
            'prodsAmount' => 'getProdsAmount',
            'publishedProds' => 'getPublisherProdsInfo',
            'releases' => 'getReleasesInfo',
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'url', 'dateCreated', 'dateModified', 'realName', 'tunesQuantity', 'picturesQuantity', 'city', 'country', 'importIds', 'aliases'}, apiShort: list{'id', 'title', 'dateModified'}, search: list{'id', 'searchTitle', 'url', 'structureType'}, zxProdsList: list{'id', 'title', 'url', 'prods', 'publishedProds', 'releases'}}
     */
    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'url',
                'dateCreated',
                'dateModified',
                'realName',
                'tunesQuantity',
                'picturesQuantity',
                'city',
                'country',
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