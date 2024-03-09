<?php

class groupAliasDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', title: 'title', searchTitle: 'getSearchTitle', url: 'getUrl', structureType: 'structureType', dateCreated: Closure(mixed):mixed, dateModified: Closure(mixed):mixed, startDate: 'startDate', endDate: 'endDate', groupId: 'groupId', importIds: 'getImportIdsIndex', prods: 'getProdsInfo', prodsAmount: 'getProdsAmount', publishedProds: 'getPublisherProdsInfo', releases: 'getReleasesInfo'}
     */
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
            'startDate' => 'startDate',
            'endDate' => 'endDate',
            'groupId' => 'groupId',
            'importIds' => 'getImportIdsIndex',
            'prods' => 'getProdsInfo',
            'prodsAmount' => 'getProdsAmount',
            'publishedProds' => 'getPublisherProdsInfo',
            'releases' => 'getReleasesInfo',
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'url', 'dateCreated', 'dateModified', 'startDate', 'endDate', 'groupId', 'importIds'}, apiShort: list{'id', 'title', 'dateModified'}, search: list{'id', 'searchTitle', 'url', 'structureType'}, zxProdsList: list{'id', 'title', 'url', 'prods', 'publishedProds', 'releases'}}
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
                'startDate',
                'endDate',
                'groupId',
                'importIds',
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