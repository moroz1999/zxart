<?php

class zxProdDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'searchTitle' => 'getSearchTitle',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'language' => 'language',
            'partyId' => 'getPartyId',
            'partyPlace' => 'partyplace',
            'compo' => 'compo',
            'year' => 'year',
            'youtubeId' => 'youtubeId',
            'description' => 'description',
            'legalStatus' => 'getLegalStatus',
            'groupsIds' => 'getGroupsIds',
            'publishersIds' => 'getPublishersIds',
            'releasesIds' => 'getReleasesIds',
            'imagesUrls' => 'getImagesUrls',
            'maps' => function ($element) {
                return $element->getFilesUrlList('mapFilesSelector', 'release');
            },
            'authorsInfo' => function ($element) {
                return $element->getAuthorsRecords('prod');
            },
            'importIds' => 'getImportIdsIndex',
            "votes" => 'votes',
            "userVote" => 'getUserVote',
            "votePercent" => 'getVotePercent',
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'url',
                'dateCreated',
                'dateModified',
                'language',
                'partyId',
                'partyPlace',
                'compo',
                'year',
                'youtubeId',
                'description',
                'legalStatus',
                'groupsIds',
                'publishersIds',
                'releasesIds',
                'imagesUrls',
                'maps',
                'authorsInfo',
                'importIds',
            ],
            'apiShort' => [
                'id',
                'dateModified',
                'releasesIds',
            ],
            'list' => [
                'id',
                'title',
                'url',
                'imagesUrls',
                "votes",
                "userVote",
                "votePercent",
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