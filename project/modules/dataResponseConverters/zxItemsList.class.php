<?php

class zxItemsListDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

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