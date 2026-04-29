<?php

class mapDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => 'title',
            'searchTitle' => function ($element) {
                if ($relatedLanguage = $element->getRelatedLanguageElement()) {
                    return $element->title . '<em class="search_title_lang">(' . $relatedLanguage->iso6393 . ')</em>';
                } else {
                    return $element->title;
                }
            }, 'url' => 'getUrl',
            'structureType' => 'structureType',
            'content' => 'content',
            'mapCode' => 'mapCode',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
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
                'url',
                'content',
                'mapCode',
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