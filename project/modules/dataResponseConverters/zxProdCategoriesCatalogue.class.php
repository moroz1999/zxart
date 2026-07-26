<?php

declare(strict_types=1);

class zxProdCategoriesCatalogueDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return array<string, string|Closure>
     */
    #[Override]
    protected function getRelationStructure(): array
    {
        $titleResolver = static function (zxProdCategoriesCatalogueElement $element): string {
            return html_entity_decode($element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        };

        return [
            'id' => 'id',
            'title' => $titleResolver,
            'h1' => $titleResolver,
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
     * @return array<string, list<string>>
     */
    #[Override]
    protected function getPresetsStructure(): array
    {
        return [
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
