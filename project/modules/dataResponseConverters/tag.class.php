<?php

class tagDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => function ($element) {
                $title = $element->title;
                if ($element->synonym) {
                    $title .= ", " . $element->synonym;
                }

                if ($element->description) {
                    $title .= " (" . $element->description . ")";
                }
                return html_entity_decode($title, ENT_QUOTES);
            },
            'value' => 'title',
            'synonym' => 'synonym',
            'description' => 'description',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'prods' => 'getProdsInfo',
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'url', 'dateCreated', 'dateModified', 'city', 'country', 'subGroupIds', 'importIds', 'aliases'}, apiShort: list{'id', 'title', 'dateModified'}, search: list{'id', 'searchTitle', 'url', 'structureType'}, zxProdsList: list{'id', 'title', 'url', 'prods', 'publishedProds', 'releases'}}
     */
    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'url',
                'value',
                'synonym',
                'description',
                'url',
            ],
            'zxProdsList' => [
                'id',
                'title',
                'url',
                'prods',
            ],
        ];
    }
}