<?php

class zxProdCategoryDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'categoriesIds' => 'getCategoriesIds',
            'prods' => 'getProdsInfo',
            'yearsSelector' => 'getYearsSelector',
            'prodsAmount' => 'getProdsAmount',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'categories',
            ],
            'search' => [
                'id',
                'title',
                'url',
                'structureType',
            ],
            'details' => [
                'id',
                'title',
                'url',
                'categories',
                'prodsAmount',
                'prods',
                'yearsSelector',
            ],
        ];
    }
}