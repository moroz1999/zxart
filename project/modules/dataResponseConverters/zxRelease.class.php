<?php

class zxReleaseDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    /**
     * @return (Closure|string)[]
     *
     * @psalm-return array{id: 'id', title: Closure(mixed):string, searchTitle: 'getSearchTitle', url: 'getUrl', structureType: 'structureType', dateCreated: Closure(zxReleaseElement):mixed, dateModified: Closure(zxReleaseElement):mixed, file: 'getFileUrl', fileName: 'fileName', year: 'year', publishersIds: 'getPublishersIds', language: 'language', languagesInfo: 'getLanguagesInfo', description: 'description', hardwareRequired: 'hardwareRequired', releaseType: 'releaseType', releaseFormat: 'releaseFormat', version: 'version', publishersInfo: 'getPublishersInfo', groupsInfo: 'getGroupsInfo', authorsInfo: Closure(zxReleaseElement):mixed, authorsInfoShort: Closure(zxReleaseElement):array, listImagesUrls: Closure(zxReleaseElement):(array|mixed), hardwareInfo: 'getHardwareInfo', inlaysUrls: 'getInlaysUrls', inlays: Closure(zxReleaseElement):array<fileElement>, ads: Closure(zxReleaseElement):array<fileElement>, instructions: Closure(zxReleaseElement):array<fileElement>, releaseStructure: Closure(zxReleaseElement):(array<never, never>|mixed), importIds: 'getImportIdsIndex', playUrl: Closure(zxReleaseElement):mixed}
     */
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
            'languagesInfo' => 'getLanguagesInfo',
            'description' => 'description',
            'hardwareRequired' => 'hardwareRequired',
            'releaseType' => 'releaseType',
            'releaseFormat' => 'releaseFormat',
            'version' => 'version',
            'publishersInfo' => 'getPublishersInfo',
            'groupsInfo' => 'getGroupsInfo',
            'authorsInfo' => function (zxReleaseElement $element) {
                return $element->getAuthorsRecords('release');
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
            'playUrl' =>function (zxReleaseElement $element) {
                return $element->getFileUrl(true);
            },
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{api: list{'id', 'title', 'dateCreated', 'dateModified', 'file', 'fileName', 'year', 'publishersIds', 'language', 'description', 'authorsInfo', 'hardwareRequired', 'releaseType', 'releaseFormat', 'version', 'inlays', 'ads', 'instructions', 'importIds', 'releaseStructure'}, list: array{0: 'id', 1: 'structureType', 2: 'title', 3: 'dateCreated', 4: 'url', 5: 'inlaysUrls', 6: 'listImagesUrls', 7: 'hardwareInfo', 8: 'year', 9: 'partyPlace', 10: 'partyInfo', 11: 'languagesInfo', 12: 'categoriesInfo', 13: 'groupsInfo', 14: 'publishersInfo', 15: 'authorsInfoShort', releaseType: 'releaseType', releaseFormat: 'releaseFormat'}, details: list{'id', 'structureType', 'title', 'playUrl', 'url'}, search: list{'id', 'searchTitle', 'url', 'structureType'}}
     */
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
        ];
    }

}