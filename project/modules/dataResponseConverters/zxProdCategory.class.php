<?php

class zxProdCategoryDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return string[]
     *
     */
    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => function ($element) {
                return html_entity_decode($element->title, ENT_QUOTES);
            },
            'h1' => function ($element) {
                return html_entity_decode($element->getH1(), ENT_QUOTES);
            },
            'structureType' => 'structureType',
            'categories' => 'getCategoriesIds',
            'prods' => 'getProdsInfo',
            'languagesSelector' => 'getLanguagesSelector',
            'formatsSelector' => 'getFormatsSelector',
            'releaseTypesSelector' => 'getReleaseTypesSelector',
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

    /**
     * @return string[][]
     *
     */
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
                'structureType',
            ],
            'zxProdsList' => [
                'id',
                'h1',
                'title',
                'prodsAmount',
                'prods',
                'languagesSelector',
                'formatsSelector',
                'releaseTypesSelector',
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
