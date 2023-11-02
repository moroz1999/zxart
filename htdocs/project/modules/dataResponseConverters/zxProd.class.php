<?php

class zxProdDataResponseConverter extends StructuredDataResponseConverter
{
    protected $defaultPreset = 'api';

    private function isMostlyPrintable($str)
    {
        $length = min(mb_strlen($str), 100);
        if (!$length) {
            return false;
        }
        $nonPrintable = 0;

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($str, $i, 1);
            if (!preg_match("/[[:print:]\p{Cyrillic}\s]/u", $char)) {
                $nonPrintable++;
            }
        }

        return ($nonPrintable / $length) < 0.1;
    }

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
            'compilationItems' => 'compilationItems',
            'seriesProds' => 'seriesProds',
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
            'maps' => function ($element) {
                return $element->getFilesUrlList('mapFilesSelector', 'release');
            },
            'rzx' => function ($element) {
                return $element->getFilesUrlList('rzx', 'release');
            },
            'authorsInfo' => function (zxProdElement $element) {
                return $element->getAuthorsRecords('prod');
            },
            'authorsInfoShort' => function (zxProdElement $element) {
                return $element->getShortAuthorship('prod');
            },
            'importIds' => 'getImportIdsIndex',
            "votes" => function ($element) {
                return (float)$element->votes;
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
            "groupsString" => function (zxProdElement $element) {
                $groups = [];
                foreach ($element->groups as $group) {
                    $groups[] = html_entity_decode($group->title, ENT_QUOTES);
                }

                return implode(', ', $groups);
            },
            "publishersString" => function (zxProdElement $element) {
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
                    $authors[] = "{$author['title']} ({$roles})";
                }
                return implode(', ', $authors);
            },
            "categoriesString" => function (zxProdElement $element) {
                $categories = [];
                foreach ($element->getCategoriesPaths() as $path) {
                    foreach ($path as $category) {
                        $categories[] = $category->title;
                    }
                }
                return implode(', ', $categories);
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
            "manualString" => function (zxProdElement $element) {
                foreach ($element->getReleasesList() as $releaseElement) {
                    foreach ($releaseElement->getFilesList('infoFilesSelector') as $fileElement) {
                        if ($fileElement->getFileExtension() === 'txt') {
                            $content = file_get_contents($fileElement->getFilePath());
                            $encoding = mb_detect_encoding($content, 'UTF-8, ISO-8859-1, IBM866, KOI8-R, Windows-1251, Windows-1252', true);

                            if ($encoding === false) {
                                continue;
                            }

                            if ($encoding !== 'UTF-8') {
                                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                            }

                            if ($content) {
                                return $content;
                            }
                        } else if ($fileElement->getFileExtension() === 'pdf') {
                            $parser = new \Smalot\PdfParser\Parser();
                            $file = $fileElement->getFilePath();
                            try {
                                $pdf = $parser->parseFile($file);
                                $textContent = $pdf->getText();
                                if ($this->isMostlyPrintable($textContent)) {
                                    return $textContent;
                                }
                            } catch (Throwable $exception) {
                                errorLog::getInstance()->logMessage(self::class, $exception->getMessage() . ' ' . $releaseElement->getTitle());
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
                        if ($item['type'] === 'file' && $item['viewable']) {
                            if ($item['internalType'] === 'plain_text') {
                                if ($file = $releaseElement->getReleaseFile($item['id'])) {
                                    return $releaseElement->getFormattedFileContent($file);
                                }
                            }
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
        ];
    }

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
                'userVote',
                'rzx',
                'externalLink',
                'compilationItems',
                'seriesProds',
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
        ];
    }
}