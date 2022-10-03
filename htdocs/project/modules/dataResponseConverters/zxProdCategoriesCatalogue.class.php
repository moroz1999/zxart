<?php

class zxProdCategoriesCatalogueDataResponseConverter extends StructuredDataResponseConverter
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
            'languagesSelector' => 'getLanguagesSelector',
            'formatsSelector' => 'getFormatsSelector',
            'countriesSelector' => 'getCountriesSelector',
            'categoriesSelector' => 'getCategoriesSelector',
            'yearsSelector' => 'getYearsSelector',
            'legalStatusesSelector' => 'getLegalStatusesSelector',
            'hardwareSelector' => 'getHardwareSelector',
            'lettersSelector' => 'getLettersSelector',
            'sortingSelector' => 'getSortingSelector',
            'tagsSelector' => 'getTagsSelector',
            'prodsAmount' => 'getProdsAmount',
            'selectorValues' => 'getSelectorValues',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'zxProdsList' => [
                'id',
                'h1',
                'title',
                'url',
                'prodsAmount',
                'prods',
                'languagesSelector',
                'formatsSelector',
                'countriesSelector',
                'tagsSelector',
                'yearsSelector',
                'categoriesSelector',
                'legalStatusesSelector',
                'hardwareSelector',
                'lettersSelector',
                'sortingSelector',
                'selectorValues',
            ],
        ];
    }
}