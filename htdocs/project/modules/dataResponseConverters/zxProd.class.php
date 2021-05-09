<?php

class zxProdDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => function ($element) {
                return html_entity_decode($element->title, ENT_QUOTES);
            },
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
            'languageTitles' => 'getLanguageTitles',
            'categoriesTitles' => 'getCategoriesTitles',
            'partyTitle' => 'getPartyTitle',
            'publishersTitles' => 'getPublisherTitles',
            'groupsTitles' => 'getGroupsTitles',
            'publishersIds' => 'getPublishersIds',
            'releasesIds' => 'getReleasesIds',
            'imagesUrls' => 'getImagesUrls',
            'listImagesUrls' => function ($element) {
                return $element->getImagesUrls('listProdImage');
            },
            'hardware' => 'getHardware',
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
                'votes',
                'userVote',
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
                'listImagesUrls',
                'hardware',
                "votes",
                "userVote",
                "votePercent",
                "year",
                "partyPlace",
                "partyTitle",
                "languageTitles",
                "categoriesTitles",
                "groupsTitles",
                "publishersTitles",
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