<?php

class newsDataResponseConverter extends StructuredDataResponseConverter
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
            'image' => 'image',
            'content' => 'content',
            'introduction' => 'introduction',
            'dateCreated' => function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => function ($element) {
                return $element->getValue('dateModified');
            },
            'introductionText' => function ($element, $scope) {
                return $scope->htmlToPlainText($element->introduction);
            },
            'contentText' => function ($element, $scope) {
                return $scope->htmlToPlainText($element->content);
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
                'image',
                'content',
                'introduction',
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
                'introductionText',
            ],
        ];
    }
}