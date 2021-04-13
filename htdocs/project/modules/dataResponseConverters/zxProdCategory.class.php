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
            'categories' => 'getCategoriesIds',
            'prods' => 'getProdsInfo',
            'yearsSelector' => 'getYearsSelector',
            'hardwareSelector' => 'getHardwareSelector',
            'lettersSelector' => 'getLettersSelector',
            'sortingSelector' => 'getSortingSelector',
            'tagsSelector' => 'getTagsSelector',
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
                'tagsSelector',
                'yearsSelector',
                'hardwareSelector',
                'lettersSelector',
                'sortingSelector',
            ],
        ];
    }
}