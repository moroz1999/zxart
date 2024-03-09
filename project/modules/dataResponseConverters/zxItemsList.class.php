<?php

class zxItemsListDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return string[]
     *
     * @psalm-return array{id: 'id', title: 'title', url: 'getUrl', structureType: 'structureType', prods: 'getProdsInfo', prodsAmount: 'getProdsAmount'}
     */
    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'prods' => 'getProdsInfo',
            'prodsAmount' => 'getProdsAmount',
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{zxProdsList: list{'id', 'title', 'url', 'prodsAmount', 'prods'}}
     */
    protected function getPresetsStructure()
    {
        return [
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