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
            'languagesInfo' => 'getLanguagesInfo',
            'categoriesInfo' => 'getCategoriesInfo',
            'partyInfo' => 'getPartyInfo',
            'publishersInfo' => 'getPublishersInfo',
            'groupsInfo' => 'getGroupsInfo',
            'publishersIds' => 'getPublishersIds',
            'releasesIds' => 'getReleasesIds',
            'inlaysUrls' => 'getInlaysUrls',
            'imagesUrls' => 'getImagesUrls',
            'listImagesUrls' => function ($element) {
                $urls = $element->getImagesUrls('prodListImage');
                if (!$urls) {
                    foreach ($element->compilationProds as $prod) {
                        $urls = array_merge($urls, $prod->getImagesUrls('prodListImage'));
                    }
                }
                return $urls;
            },
            'hardware' => 'getHardware',
            'hardwareInfo' => 'getHardwareInfo',
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
            "denyVoting" => 'denyVoting',
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
                'dateCreated',
                'url',
                'inlaysUrls',
                'listImagesUrls',
                'hardwareInfo',
                "votes",
                "userVote",
                "year",
                "partyPlace",
                "partyInfo",
                "languagesInfo",
                "categoriesInfo",
                "groupsInfo",
                "publishersInfo",
                "youtubeId",
                "denyVoting",
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