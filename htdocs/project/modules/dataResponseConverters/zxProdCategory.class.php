<?php

class zxProdCategoryDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'h1' => 'getH1',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'categoriesIds' => 'getCategoriesIds',
            'prods' => 'getProdsInfo',
            'yearsSelector' => 'getYearsSelector',
            'lettersSelector' => 'getLettersSelector',
            'sortingSelector' => 'getSortingSelector',
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
                'h1',
                'title',
                'url',
                'categories',
                'prodsAmount',
                'prods',
                'yearsSelector',
                'lettersSelector',
                'sortingSelector',
            ],
        ];
    }
}