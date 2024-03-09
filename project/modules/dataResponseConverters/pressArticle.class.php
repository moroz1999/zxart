<?php

class pressArticleDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', structureType: 'structureType', title: Closure(mixed):string, introduction: 'introduction', content: 'content', url: 'getUrl', searchTitle: 'getSearchTitle'}
     */
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

    /**
     * @return string[][]
     *
     * @psalm-return array{ai: list{'title', 'introduction', 'content'}, api: list{'id', 'title', 'introduction', 'content'}, search: list{'id', 'searchTitle', 'url', 'structureType'}}
     */
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