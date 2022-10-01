<?php

class zxReleaseDataResponseConverter extends StructuredDataResponseConverter
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
            'listImagesUrls' => function (zxReleaseElement $element) {
                $urls = $element->getImagesUrls('prodListImage');
                if (!$urls) {
                    if ($prod = $element->getProd()) {
                        $urls = $prod->getImagesUrls('prodListImage');
                    }
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