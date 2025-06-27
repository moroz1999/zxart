<?php

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
            'url' => 'getUrl',
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
            'playableFiles' => 'getPlayableFiles',
            'authorsInfo' => function (zxReleaseElement $element) {
                return $element->getAuthorsRecords('release');
            },
            'prodId' => function (zxReleaseElement $element): int {
                return $element->getProd()?->id ?? 0;
            },
            'authorsInfoShort' => function (zxReleaseElement $element) {
                return $element->getShortAuthorship('release');
            },
            'listImagesUrls' => function (zxReleaseElement $element) {
                $preset = $element->getListImagePreset();
                $urls = $element->getImagesUrls($preset);
                if ($prod = $element->getProd()) {
                    $urls = array_merge($urls, $prod->getImagesUrls($preset));
                }

                return $urls;
            },
            'hardwareInfo' => 'getHardwareInfo',
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
                'releaseStructure',
            ],
            'list' => [
                'id',
                'structureType',
                'title',
                'dateCreated',
                'url',
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
                'url',
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
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
                'playableFiles',
                'prodId',
                'releaseType',
                'year',
                'language',
                'publishersInfo',
            ]
        ];
    }

}