<?php

class zxPictureDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', title: 'title', url: 'getUrl', structureType: 'structureType', dateCreated: Closure(mixed):mixed, dateModified: Closure(mixed):mixed, partyId: 'getPartyId', compo: 'compo', partyPlace: 'partyplace', authorIds: 'getAuthorIds', tags: 'getTagsTexts', type: 'type', rating: 'votes', views: 'views', year: 'year', description: 'description', imageUrl: 'getImageUrl', originalUrl: Closure(mixed):(false|string)}
     */
    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'url' => 'getUrl',
            'searchTitle' => 'getSearchTitle',
            'structureType' => 'structureType',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'partyId' => 'getPartyId',
            'compo' => 'compo',
            'partyPlace' => 'partyplace',
            'authorIds' => 'getAuthorIds',
            'tags' => 'getTagsTexts',
            'type' => 'type',
            'rating' => 'votes',
            'views' => 'views',
            'year' => 'year',
            'description' => 'description',
            'imageUrl' => 'getImageUrl',
            'originalUrl' => function ($element) {
                if ($element->image) {
                    return controller::getInstance(
                        )->baseURL . 'file/id:' . $element->id . '/filename:' . $element->getFileName(
                            'original',
                            true,
                            false
                        );
                }
                return false;
            },
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'url', 'dateCreated', 'dateModified', 'partyId', 'compo', 'partyPlace', 'authorIds', 'tags', 'type', 'rating', 'views', 'year', 'description', 'imageUrl', 'originalUrl'}, apiShort: list{'id', 'dateModified'}, search: list{'id', 'title', 'url', 'structureType'}}
     */
    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'url',
                'dateCreated',
                'dateModified',
                'partyId',
                'compo',
                'partyPlace',
                'authorIds',
                'tags',
                'type',
                'rating',
                'views',
                'year',
                'description',
                'imageUrl',
                'originalUrl',
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