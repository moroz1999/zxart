<?php

use ZxArt\Shared\EntityType;

class zxReleaseDataResponseConverter extends StructuredDataResponseConverter
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
            'structureType' => 'structureType',
            'dateCreated' => function (zxReleaseElement $element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function (zxReleaseElement $element) {
                return $element->getValue('dateModified');
            },
            'file' => 'getFileUrl',
            'fileName' => 'fileName',
            'year' => 'year',
            'publishersIds' => 'getPublishersIds',
            'language' => 'language',
            'description' => 'description',
            'hardwareRequired' => 'hardwareRequired',
            'releaseType' => 'releaseType',
            'releaseFormat' => 'releaseFormat',
            'version' => 'version',
            'publishersInfo' => 'getPublishersInfo',
            'groupsInfo' => 'getGroupsInfo',
            'playableFiles' => 'getArchiveFilesForHardware',
            'authorsInfo' => function (zxReleaseElement $element) {
                return $element->getAuthorsRecords(EntityType::Release->value);
            },
            'prodId' => function (zxReleaseElement $element): int {
                return $element->getProd()?->id ?? 0;
            },
            'authorsInfoShort' => function (zxReleaseElement $element) {
                return $element->getShortAuthorship(EntityType::Release->value);
            },
            'listImagesUrls' => function (zxReleaseElement $element) {
                $preset = $element->getListImagePreset();
                $urls = $element->getImagesUrls($preset);
                if ($prod = $element->getProd()) {
                    $urls = array_merge($urls, $prod->getImagesUrls($preset));
                }

                return $urls;
            },
            // effective, matching what the site shows: `hardwareRequired` right
            // above is still the release's own codes, but on their own they are
            // empty for most releases and say nothing about what this one runs on
            'hardwareInfo' => static function (zxReleaseElement $element): array {
                return $element->getRunsOnHardwareDetails();
            },
            'inlaysUrls' => 'getInlaysUrls',
            'inlays' => function (zxReleaseElement $element) {
                return $element->getFilesUrlList('inlayFilesSelector', 'release');
            },
            'ads' => function (zxReleaseElement $element) {
                return $element->getFilesUrlList('adFilesSelector', 'release');
            },
            'instructions' => function (zxReleaseElement $element) {
                return $element->getFilesUrlList('infoFilesSelector', 'release');
            },
            'releaseStructure' => function (zxReleaseElement $element) {
                if ($element->parsed && $element->isDownloadable()) {
                    if ($structure = $element->getReleaseStructure()) {
                        return $structure;
                    }
                }
                return [];
            },
            'importIds' => 'getImportIdsIndex',
            'votes' => 'getVotes',
            'votesAmount' => 'getVotesAmount',
            'userVote' => 'getUserVote',
            'archiveFiles' => 'getArchiveFilesForHardware',
            'playUrl' => function (zxReleaseElement $element) {
                return $element->getFileUrl(true);
            },
        ];
    }

    protected function getPresetsStructure()
    {
        return [
            'api' => [
                'id',
                'title',
                'dateCreated',
                'dateModified',
                'file',
                'fileName',
                'year',
                'publishersIds',
                'language',
                'description',
                'authorsInfo',
                'hardwareRequired',
                'releaseType',
                'releaseFormat',
                'version',
                'inlays',
                'ads',
                'instructions',
                'importIds',
                'votes',
                'votesAmount',
                'userVote',
                'releaseStructure',
                'prodId',
            ],
            'list' => [
                'id',
                'structureType',
                'title',
                'dateCreated',
                'inlaysUrls',
                'listImagesUrls',
                'hardwareInfo',
                "year",
                "partyPlace",
                "partyInfo",
                "languagesInfo",
                "categoriesInfo",
                "groupsInfo",
                "publishersInfo",
                "authorsInfoShort",
                'releaseType' => 'releaseType',
                'releaseFormat' => 'releaseFormat',
            ],
            'details' => [
                'id',
                'structureType',
                'title',
                'playUrl',
            ],
            'search' => [
                'id',
                'searchTitle',
                'structureType',
            ],
            'zxdb' => [
                'id',
                'title',
                'playableFiles',
                'authorsInfoShort',
                'publishersInfo',
                'releaseType',
                'language',
                'year'
            ],
            'offline' => [
                'id',
                'title',
                'dateModified',
                'prodId',
                'releaseType',
                'year',
                'language',
                'authorsInfoShort',
                'publishersInfo',
                'archiveFiles',
                'hardwareRequired',
                'version',
            ]
        ];
    }

}
