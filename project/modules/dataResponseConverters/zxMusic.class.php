<?php

class zxMusicDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', title: 'title', internalTitle: 'internalTitle', url: 'getUrl', structureType: 'structureType', dateCreated: Closure(mixed):mixed, dateModified: Closure(mixed):mixed, autoplayUrl: Closure(mixed):string, time: 'time', partyId: 'getPartyId', compo: 'compo', partyPlace: 'partyplace', authorIds: 'getAuthorIds', tags: 'getTagsTexts', type: 'type', rating: 'votes', plays: 'plays', year: 'year', description: 'description', mp3FilePath: 'getMp3FilePath', originalUrl: Closure(mixed):(false|string), originalFileName: Closure(mixed):mixed}
     */
    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'searchTitle' => 'getSearchTitle',
            'internalTitle' => 'internalTitle',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'autoplayUrl' => function ($element) {
                return $element->getUrl() . 'autoplay:1/';
            },
            'time' => 'time',
            'partyId' => 'getPartyId',
            'compo' => 'compo',
            'partyPlace' => 'partyplace',
            'authorIds' => 'getAuthorIds',
            'tags' => 'getTagsTexts',
            'type' => 'type',
            'rating' => 'votes',
            'plays' => 'plays',
            'year' => 'year',
            'description' => 'description',
            'mp3FilePath' => 'getMp3FilePath',
            'originalUrl' => function ($element) {
                if ($element->getFileName('original')) {
                    return controller::getInstance(
                        )->baseURL . 'file/id:' . $element->file . '/filename:' . $element->getFileName('original');
                }
                return false;
            },
            'originalFileName' => function ($element) {
                return $element->getFileName('original');
            },
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'internalTitle', 'url', 'dateCreated', 'dateModified', 'time', 'partyId', 'compo', 'partyPlace', 'authorIds', 'tags', 'type', 'rating', 'plays', 'year', 'description', 'imageUrl', 'originalUrl', 'originalFileName', 'mp3FilePath'}, apiShort: list{'id', 'dateModified'}, search: list{'id', 'title', 'url', 'structureType'}}
     */
    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'internalTitle',
                'url',
                'dateCreated',
                'dateModified',
                'time',
                'partyId',
                'compo',
                'partyPlace',
                'authorIds',
                'tags',
                'type',
                'rating',
                'plays',
                'year',
                'description',
                'imageUrl',
                'originalUrl',
                'originalFileName',
                'mp3FilePath',
            ],
            'apiShort' => [
                'id',
                'dateModified',
            ],
            'search' => [
                'id',
                'title',
                'url',
                'structureType',
                'searchTitle',
            ],
        ];
    }
}