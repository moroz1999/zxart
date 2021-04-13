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
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
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
            'authorsInfo' => function ($element) {
                return $element->getAuthorsRecords('release');
            },
            'inlays' => function ($element) {
                return $element->getFilesUrlList('inlayFilesSelector', 'release');
            },
            'ads' => function ($element) {
                return $element->getFilesUrlList('adFilesSelector', 'release');
            },
            'instructions' => function ($element) {
                return $element->getFilesUrlList('infoFilesSelector', 'release');
            },
            'releaseStructure' => function ($element) {
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
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
            ],
        ];
    }

}