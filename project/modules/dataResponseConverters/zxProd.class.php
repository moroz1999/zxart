<?php

class zxProdDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    protected function getRelationStructure()
    {
        return [
            'id' => 'id',
            'title' => static function ($element) {
                return html_entity_decode($element->title, ENT_QUOTES);
            },
            'searchTitle' => 'getSearchTitle',
            'url' => 'getUrl',
            'structureType' => 'structureType',
            'dateCreated' => static function ($element) {
                return $element->getValue('dateCreated');
            },
            'dateModified' => static function ($element) {
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
            'rootCategoriesInfo' => 'getRootCategoriesInfo',
            'partyInfo' => 'getPartyInfo',
            'publishersInfo' => 'getPublishersInfo',
            'groupsInfo' => 'getGroupsInfo',
            'publishersIds' => 'getPublishersIds',
            'releasesIds' => 'getReleasesIds',
            'imagesUrls' => 'getImagesUrls',
            'compilationItems' => 'compilationItems',
            'seriesProds' => 'seriesProds',
            'inlaysUrls' => 'getInlaysUrls',
            'inlays' => function (zxReleaseElement $element) {
                return $element->getFilesUrlList('inlayFilesSelector', 'release');
            },
            'listImagesUrls' => function (zxProdElement $element) {
                $preset = $element->getListImagePreset();
                $urls = $element->getImagesUrls($preset);
                foreach ($element->compilationItems as $prod) {
                    if ($prodUrls = $prod->getImagesUrls($preset)) {
                        $urls[] = reset($prodUrls);
                    }
                }
                foreach ($element->seriesProds as $prod) {
                    if ($prodUrls = $prod->getImagesUrls($preset)) {
                        $urls[] = reset($prodUrls);
                    }
                }
                return $urls;
            },
            'hardware' => 'getHardware',
            'hardwareInfo' => 'getHardwareInfo',
            'maps' => static function ($element) {
                return $element->getFilesUrlList('mapFilesSelector', 'release');
            },
            'rzx' => static function ($element) {
                return $element->getFilesUrlList('rzx', 'release');
            },
            'authorsInfo' => function (zxProdElement $element) {
                return $element->getAuthorsRecords('prod');
            },
            'authorsInfoShort' => function (zxProdElement $element) {
                return $element->getShortAuthorship('prod');
            },
            'importIds' => 'getImportIdsIndex',
            "votes" => static function ($element) {
                return (float)$element->votes;
            },
            "votesAmount" => static function ($element) {
                return (int)$element->votesAmount;
            },
            "partyString" => function (zxProdElement $element) {
                $partyString = '';
                if ($element->party) {
                    $party = $element->getPartyElement();
                    if ($element->partyplace) {
                        $partyString .= $element->partyplace . ' at ';
                    }
                    $partyString .= $party->getTitle();
                }
                return $partyString;
            },
            "groupsString" => static function (zxProdElement $element) {
                $groups = [];
                foreach ($element->groups as $group) {
                    $groups[] = html_entity_decode($group->title, ENT_QUOTES);
                }

                return implode(', ', $groups);
            },
            "publishersString" => static function (zxProdElement $element) {
                $publishers = [];
                foreach ($element->publishers as $publisher) {
                    $publishers[] = html_entity_decode($publisher->title, ENT_QUOTES);
                }

                return implode(', ', $publishers);
            },
            "authorsInfoString" => function (zxProdElement $element) {
                $authors = [];
                foreach ($element->getShortAuthorship('prod') as $author) {
                    $roles = implode(', ', $author['roles']); // concatenate roles with a comma
                    if ($roles === 'unknown') {
                        $authors[] = $author['title'];
                    } else {
                        $authors[] = "{$author['title']} ({$roles})";
                    }
                }
                return implode(', ', $authors);
            },
            "categoriesString" => function (zxProdElement $element) {
                $paths = [];
                foreach ($element->getCategoriesPaths() as $path) {
                    $categories = [];
                    foreach ($path as $category) {
                        $categories[] = $category->title;
                    }
                }
                $paths[] = implode('/', $categories);
                return implode('; ', $paths);
            },
            "languageString" => function (zxProdElement $element) {
                $languages = [];
                foreach ($element->getLanguagesInfo() as $language) {
                    $languages[] = $language['title'];
                }
                return implode(', ', $languages);
            },
            "hardwareString" => function (zxProdElement $element) {
                $hwList = [];

                foreach ($element->getHardwareInfo(false) as $hardware) {
                    $hwList[] = $hardware['title'];
                }
                return implode(', ', $hwList);
            },
            "articleIntros" => static function (zxProdElement $element) {
                $articlesList = [];

                foreach ($element->articles as $article) {
                    $articlesList[] = ['title' => $article->title, 'description' => $article->introduction];
                }
                return $articlesList;
            },
            "manualString" => function (zxProdElement $element) {
                foreach ($element->getReleasesList() as $releaseElement) {
                    foreach ($releaseElement->getFilesList('infoFilesSelector') as $fileElement) {
                        if ($fileElement->getFileExtension() === 'txt') {
                            $content = file_get_contents($fileElement->getFilePath());
                            $content = EncodingDetector::decodeText($content);

                            if ($content) {
                                return $content;
                            }
                        } elseif ($fileElement->getFileExtension() === 'pdf') {
                            $parser = new \Smalot\PdfParser\Parser();
                            $file = $fileElement->getFilePath();
                            try {
                                $pdf = $parser->parseFile($file);
                                $textContent = $pdf->getText();

                                if (EncodingDetector::isMostlyPrintable($textContent)) {
                                    return $textContent;
                                }
                            } catch (Throwable $exception) {
                                ErrorLog::getInstance()->logMessage(self::class, $exception->getMessage() . ' ' . $releaseElement->getTitle());
                            }

                            return '';
                        }
                    }
                }
                return '';
            },
            "releaseFileDescription" => function (zxProdElement $element) {
                foreach ($element->getReleasesList() as $releaseElement) {
                    foreach ($releaseElement->getReleaseFlatStructure() as $item) {
                        if ($item['type'] === 'file' &&
                            $item['viewable'] &&
                            $item['internalType'] === 'plain_text' &&
                            $item['encoding'] !== 'none'
                        ) {
                            return $releaseElement->getFormattedFileContent($item);
                        }
                    }
                }
                return '';
            },
            "isPlayable" => function (zxProdElement $element) {
                foreach ($element->getReleasesList() as $releaseElement) {
                    if ($releaseElement->isPlayable()) {
                        return true;
                    }
                }
                return false;
            },
            "userVote" => 'getUserVote',
            "votePercent" => 'getVotePercent',
            "denyVoting" => 'denyVoting',
            "externalLink" => 'externalLink',
            "connectedCategoriesIds" => 'getConnectedCategoriesIds',
        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{ai: list{'title', 'description', 'languageString', 'partyString', 'year', 'description', 'groupsString', 'publishersString', 'authorsInfoString', 'hardwareString', 'manualString', 'releaseFileDescription', 'categoriesString', 'isPlayable', 'compilationItems', 'seriesProds'}, api: list{'id', 'title', 'url', 'dateCreated', 'dateModified', 'language', 'partyId', 'partyPlace', 'compo', 'year', 'youtubeId', 'description', 'legalStatus', 'groupsIds', 'publishersIds', 'releasesIds', 'imagesUrls', 'maps', 'authorsInfo', 'importIds', 'votes', 'userVote', 'rzx', 'externalLink', 'compilationItems', 'seriesProds'}, apiShort: list{'id', 'dateModified', 'releasesIds'}, list: list{'id', 'structureType', 'title', 'dateCreated', 'url', 'inlaysUrls', 'listImagesUrls', 'hardwareInfo', 'votes', 'userVote', 'year', 'partyPlace', 'partyInfo', 'languagesInfo', 'categoriesInfo', 'groupsInfo', 'publishersInfo', 'authorsInfoShort', 'youtubeId', 'denyVoting', 'legalStatus', 'externalLink'}, search: list{'id', 'searchTitle', 'url', 'structureType'}}
     */
    protected function getPresetsStructure()
    {
        return [
            'ai' => [
                'title',
                'description',
                'languageString',
                'partyString',
                'year',
                'description',
                'groupsString',
                'publishersString',
                'authorsInfoString',
                'hardwareString',
                'manualString',
                'releaseFileDescription',
                'categoriesString',
                'isPlayable',
                'compilationItems',
                'seriesProds',
                'articleIntros',
            ],
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
                'votesAmount',
                'userVote',
                'rzx',
                'externalLink',
                'compilationItems',
                'seriesProds',
                'connectedCategoriesIds',
                'categoriesString',
            ],
            'aiCategories' => [
                'title',
                'year',
                'manualString',
                'groupsString',
                'publishersString',
            ],
            'apiShort' => [
                'id',
                'dateModified',
                'releasesIds',
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
                "votes",
                "votesAmount",
                "userVote",
                "year",
                "partyPlace",
                "partyInfo",
                "languagesInfo",
                "categoriesInfo",
                "groupsInfo",
                "publishersInfo",
                "authorsInfoShort",
                "youtubeId",
                "denyVoting",
                "legalStatus",
                "externalLink",
            ],
            'search' => [
                'id',
                'searchTitle',
                'url',
                'structureType',
            ],
            'offline' => [
                'id',
                'title',
                'dateModified',
                'year',
                'legalStatus',
                'rootCategoriesInfo',
                'language',
                'groupsInfo',
                'publishersInfo',
            ]
        ];
    }
}