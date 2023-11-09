<?php

class pressArticleDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'structureType' => 'structureType',
            'title' => function ($element) {
                return html_entity_decode($element->title, ENT_QUOTES);
            },
            "introduction" => 'introduction',
            "content" => 'content',
            'url' => 'getUrl',
            'searchTitle' => 'getSearchTitle',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'ai' => [
                'title',
                'introduction',
                'content',
            ],
            'api' => [
                'id',
                'title',
                'introduction',
                'content',
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
            ],
        ];
    }
}