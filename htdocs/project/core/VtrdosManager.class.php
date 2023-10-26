<?php

class VtrdosManager extends errorLogger
{
    protected $counter = 0;
    protected $maxCounter = 50;
    /**
     * @var ProdsManager
     */
    protected $prodsManager;
    /**
     * @var AuthorsManager
     */
    protected $authorsManager;
    /**
     * @var GroupsManager
     */
    protected $groupsManager;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    protected $urlsSettings = [];
    protected $origin = 'vt';
    protected $rootUrl = 'https://vtrd.in/';
    protected $alphabet = [
        '123',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    protected $hwIndex = [
        '(DMA UltraSound Card)' => 'dmausc',
        '(km)' => 'kempstonmouse',
        '(kemp8bit)' => 'kempston8b',
        '(cvx)' => 'covoxfb',
        '(dma)' => 'dmausc',
        '(gs)' => 'gs',
        '(sd)' => 'soundrive',
        '(for 48k)' => 'zx48',
        '(48k only)' => 'zx48',
        '(128k only)' => 'zx128',
        '(1024k)' => 'pentagon1024',
        '(256k)' => 'scorpion',
        '(ts)' => 'ts',
        'hdd' => 'hdd',
        '48k' => 'zx48',
        'Pentagon 512k' => 'pentagon512',
        'Pentagon 1024k' => 'pentagon1024',
        'Pentagon 1024SL' => 'pentagon1024',
        'Scorpion ZS 256' => 'scorpion',
        'Byte' => 'byte',
        'ATM Turbo' => 'atm',
        'smuc' => 'smuc',
        'SMUC' => 'smuc',
        'Sprinter' => 'sprinter',
        'Covox' => 'covoxfb',
        'General Sound' => 'gs',
        'Cache' => 'Cache',
        'SounDrive' => 'soundrive',
        'Turbo Sound' => 'ts',
        'TurboSound FM' => 'tsfm',
        'ZXM-MoonSound' => 'zxm',
        'AY' => 'ay',
        'DMA UltraSound Card' => 'dmausc',
        'DMA USC' => 'dmausc',
        'Beeper' => 'beeper',
        'AY Mouse' => 'aymouse',
        'CP/M' => 'cpm',
        '(for Profi)' => 'profi',
        '(Base Conf)' => 'baseconf',
        '(TS Conf)' => 'tsconf',
        'ATM Turbo 2' => 'atm2',
        'neoGS-SD' => 'sdneogs',
        'NEMO-HDD' => 'nemoide',
        'Nemo HDD' => 'nemoide',
        'ZSD' => 'zcontroller',
        'iS-DOS' => 'isdos',
        'TASiS' => 'tasis',
        'CD-ROM' => 'cd',
        'CD' => 'cd',
        'GMX' => 'gmx',
    ];

    protected $categoryHardware = [
        'Инфа и утилиты для TurboSound FM:' => 'tsfm',
        'Инфа, игры, утилиты для DMA Ultrasound Card:' => 'dmausc',
        'Софт для Profi компьютера:' => 'profi',
        'Дисковая операционная система CP/M:' => 'cpm',
        'Дисковая система iS-DOS от питерской IskraSoft:' => 'isdos',
        'Информационные сборники Евгения Илясова (под iS-DOS):' => 'isdos',
        'Анекдоты, юмор (под iS-DOS):' => 'isdos',
    ];
    protected $categories = [
        'Архиваторы:' => 244886,
        'Ассемблеры:' => 92552,
        'Буты:' => 204150,
        'Графические Редакторы:' => 244860,
        'Графические Утилиты:' => 92587,
        'Дебаггеры:' => 244863,
        'Дисковые утилиты:' => 244864,
        'Записные книжки:' => 244865,
        'Каталогизаторы:' => 92569,
        'Командеры:' => 92187,
        'Конвертеры Экранов:' => 244866,
        'Копир MS-DOS - TR-DOS:' => 244867,
        'Копировщики:' => 244869,
        'Копировщики Disk -> Tape:' => 244868,
        'Музыкальные Плейеры:' => 244870,
        'Музыкальные редакторы для AY:' => 244871,
        'Музыкальные редакторы для beeper:' => 244872,
        'Музыкальные Утилиты для работы с AY-звуком:' => 244873,
        'Музыкальные утилиты для работы с цифровым звуком:' => 244874,
        'Операционные системы:' => 244875,
        'Программаторы:' => 244876,
        'Просмотрщики графики:' => 244862,
        'Просмотрщики текстов:' => 244877,
        'Прошивки ПЗУ:' => 244878,
        'Работа с сетями:' => 202587,
        'Разное:' => 92590,
        'Редакторы звуков:' => 244881,
        'Редакторы игр:' => 202586,
        'Редакторы Шрифтов:' => 92573,
        'Системные тесты:' => 244882,
        'Спрайтовые Редакторы:' => 244883,
        'Текстовые редакторы:' => 244884,
        'Универсальные просмотрщики:' => 244885,
        'Упаковщики данных:' => 244887,
        'Упаковщики экранов:' => 244888,
        'Утилиты для защиты данных:' => 244890,
        'Утилиты для работы с текстом:' => 244889,
        'Утилиты для снятия защиты:' => 244891,
        'Цифровые музыкальные редакторы:' => 244892,
        'Языки программирования:' => 244893,
        'Различные тексты:' => 92181,
        'Статьи и правила конкурсов:' => 92181,
        'Сборники разной музыки:' => 92175,
        'Инфа и утилиты для TurboSound FM:' => 244871,
        'Инфа, игры, утилиты для DMA Ultrasound Card:' => 244871,
        'Схемы и девайсы:' => 92181,
        'Дисковая операционная система CP/M:' => 244875,
        'Софт для Profi компьютера:' => 92183,
        'Софт для клона ZX-Evolution:' => 92177,
        'Софт для ATM Turbo компьютера:' => 92177,
        'Дисковая система iS-DOS от питерской IskraSoft:' => 92183,
        'Информационные сборники Евгения Илясова (под iS-DOS):' => 92181,
        'Анекдоты, юмор (под iS-DOS):' => 92181,
        'Сборники игрушек, не вписывающиеся в мои таблицы:' => 202590,
        'Пакетное создание прессы на ZX:' => 418662,
        'Графика, гифты и поздравления:' => 92172,
        'Хиромантия, гадания и прочее:' => 92582,
        'Различные психологические тесты:' => 418663,
        'Разнообразный софт:' => 92188,
        'Game // exUSSR' => 244871,
        'Game // English' => 244871,
        'Game // Demo version' => 244871,
        'Game // Translated' => 244871,
        'GS' => 244871,
    ];

    public function __construct()
    {
//        $this->urlsSettings['https://vtrd.in/press.php?l=1'] = [
//            [
//                'type' => 'press',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/press.php?l=2'] = [
//            [
//                'type' => 'press',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/updates.php'] = [
//            [
//                'type' => 'updates',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=translat'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'language' => ['ru'],
//                    'releaseType' => 'localization',
//                    'authorRoles' => ['localization', 'release'],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=full_ver'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=demo_ver'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'demoversion',
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/system.php'] = [
//            [],
//            [
//                'type' => 'system',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/sbor.php'] = [
//            [],
//            [
//                'type' => 'sbor',
//                'release' => [
//                    'hardwareRequired' => [],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/gs.php'] = [
//            [],
//            [
//                'type' => 'list',
//                'prod' => [
//                    'directCategories' => [92581],
//                ],
//                'release' => [
//                    'hardwareRequired' => ['gs'],
//                ],
//            ],
//            [],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'hardwareRequired' => ['gs'],
//                ],
//            ],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'hardwareRequired' => ['gs'],
//                    'authorRoles' => ['sfx', 'release'],
//                ],
//            ],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'hardwareRequired' => ['gs'],
//                    'authorRoles' => ['intro_code', 'release'],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=remix'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'authorRoles' => ['adaptation', 'release'],
//                ],
//            ],
//        ];
//        foreach ($this->alphabet as $item) {
//            $this->urlsSettings['https://vtrd.in/games.php?t=' . $item] = [
//                [
//                    'type' => 'table',
//                    'prod' => [
//                        'directCategories' => [92177],
//                    ],
//                    'release' => [
//                        'releaseType' => 'adaptation',
//                        'authorRoles' => ['adaptation', 'release'],
//                    ],
//                ],
//            ];
//        }
    }

    /**
     * @param AuthorsManager $authorsManager
     */
    public function setAuthorsManager($authorsManager)
    {
        $this->authorsManager = $authorsManager;
    }

    /**
     * @param GroupsManager $groupsManager
     */
    public function setGroupsManager($groupsManager)
    {
        $this->groupsManager = $groupsManager;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager)
    {
        $this->countriesManager = $countriesManager;
    }

    /**
     * @param mixed $prodsManager
     */
    public function setProdsManager(ProdsManager $prodsManager)
    {
        $this->prodsManager = $prodsManager;
    }

    public function importAll()
    {
        foreach ($this->urlsSettings as $url => $settings) {
            $this->importUrlProds($url, $settings);
        }
    }

    public function importUrlProds($pageUrl, $allSettings)
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $tableNodes = $xPath->query("//table");
            foreach ($tableNodes as $tableNode) {
                $settings = array_shift($allSettings);
                if (!empty($settings['type'])) {
                    if ($settings['type'] === 'list') {
                        $this->parseList($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'system') {
                        $this->parseSystem($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'sbor') {
                        $this->parseSbor($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'table') {
                        $this->parseTable($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'updates') {
                        $this->parseUpdates($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'press') {
                        $this->parsePress($tableNode, $xPath, $settings);
                    }
                }
            }
        }
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseTable($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $releaseNodes = $xPath->query(".//tr", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                $tdNodes = $releaseNode->getElementsByTagName('td');
                if ($td1 = $tdNodes->item(0)) {
                    $url = false;
                    $prodTitle = false;
                    $prodId = false;

                    if ($aNodes = $td1->getElementsByTagName('a')) {
                        foreach ($aNodes as $aNode) {
                            if (!str_contains($aNode->getAttribute('class'), 'details')) {
                                $url = $aNode->getAttribute('href');
                                $prodTitle = $this->processTitle($aNode->textContent);
                                $prodId = md5($prodTitle);
                            }
                        }
                    }
                    if ($url && $prodTitle) {
                        $releaseInfo = [];

                        if (!isset($prodsIndex[$prodId])) {
                            $prodsIndex[$prodId] = [
                                'id' => $prodId,
                                'title' => $prodTitle,
                                'labels' => [],
                                'releases' => [],
                            ];
                        }
                        $releaseInfo['fileUrl'] = $this->rootUrl . $url;
                        $releaseInfo['id'] = md5(basename($url));
                        $releaseInfo['labels'] = [];

                        if ($td2 = $tdNodes->item(1)) {
                            $this->parseAuthorsString((string)$td2->nodeValue, $prodsIndex[$prodId]);
                        }
                        $roles = [];
                        if (isset($settings['release'])) {
                            if (isset($settings['release']['authorRoles'])) {
                                $roles = $settings['release']['authorRoles'];
                            }
                        }
                        $roles[] = 'release';
                        if ($td3 = $tdNodes->item(2)) {
                            $this->parseAuthorsString((string)$td3->nodeValue, $releaseInfo, $roles, 'publishers');
                        }
                        if (isset($settings['release'])) {
                            if (isset($settings['release']['language'])) {
                                $releaseInfo['language'] = $settings['release']['language'];
                            }
                            if (isset($settings['release']['releaseType'])) {
                                $releaseInfo['releaseType'] = $settings['release']['releaseType'];
                            }
                            if (isset($settings['release']['hardwareRequired'])) {
                                $releaseInfo['hardwareRequired'] = $settings['release']['hardwareRequired'];
                            }
                        }
                        if (isset($settings['prod'])) {
                            if (isset($settings['prod']['directCategories'])) {
                                $prodsIndex[$prodId]['directCategories'] = $settings['prod']['directCategories'];
                            }
                        }
                        $this->parseANode($aNode, $releaseInfo);
                        $prodsIndex[$prodId]['releases'][] = $releaseInfo;
                    }
                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parsePress($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $rowNodes = $xPath->query(".//tr", $node);
        if ($rowNodes->length > 0) {
            foreach ($rowNodes as $rowNode) {
                $tdNodes = $rowNode->getElementsByTagName('td');
                if ($td1 = $tdNodes->item(0)) {
                    $pressTitle = '';

                    if ($bNodes = $td1->getElementsByTagName('b')) {
                        foreach ($bNodes as $bNode) {
                            $pressTitle = trim($bNode->textContent);
                        }
                    }
                    if ($pressTitle && ($td2 = $tdNodes->item(1))) {
                        if ($aNodes = $td2->getElementsByTagName('a')) {
                            $seriesProdsIds = [];
                            if ($aNodes->length > 20) {
                                $directCategories = [92182];
                            } else {
                                $directCategories = [92179];
                            }
                            foreach ($aNodes as $aNode) {
                                $url = false;
                                $prodTitle = false;
                                $prodId = false;
                                if (!str_contains($aNode->getAttribute('class'), 'rpad')) {
                                    $url = $aNode->getAttribute('href');
                                    $prodTitle = $this->processTitle($pressTitle . ' #' . $aNode->textContent);
                                    $prodId = md5($pressTitle . ' #' . $aNode->textContent);
                                }


                                if ($url && $prodTitle) {
                                    $releaseInfo = [];

                                    if (!isset($prodsIndex[$prodId])) {
                                        $prodsIndex[$prodId] = [
                                            'id' => $prodId,
                                            'title' => $prodTitle,
                                            'labels' => [],
                                            'releases' => [],
                                        ];
                                        $prodsIndex[$prodId]['directCategories'] = $directCategories;
                                    }
                                    $seriesProdsIds[] = $prodId;
                                    $releaseInfo['fileUrl'] = $this->rootUrl . $url;
                                    $releaseInfo['title'] = $prodTitle;
                                    $releaseInfo['id'] = md5(basename($url));
                                    $releaseInfo['releaseType'] = 'original';
                                    $releaseInfo['labels'] = [];
                                    $releaseInfo['language'] = ['ru'];
                                    $releaseInfo['hardwareRequired'] = ['pentagon128'];

                                    $prodsIndex[$prodId]['releases'][] = $releaseInfo;
                                }

                            }
                            if ($seriesProdsIds) {
                                $seriesProdId = md5($pressTitle);

                                $prodsIndex[$seriesProdId] = [
                                    'id' => $seriesProdId,
                                    'title' => $pressTitle,
                                    'seriesProds' => $seriesProdsIds,
                                    'directCategories' => $directCategories
                                ];

                            }
                        }

                    }

                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseUpdates($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $releaseNodes = $xPath->query(".//tr", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                $tdNodes = $releaseNode->getElementsByTagName('td');
                if ($td2 = $tdNodes->item(1)) {
                    $url = false;
                    $prodTitle = false;
                    $prodId = false;
                    $currentCategory = false;

                    if ($aNodes = $td2->getElementsByTagName('a')) {
                        foreach ($aNodes as $aNode) {
                            if (!str_contains($aNode->getAttribute('class'), 'details')) {
                                $url = $aNode->getAttribute('href');
                                $prodTitle = $this->processTitle($aNode->textContent);
                                $prodId = md5($prodTitle);
                                break;
                            }
                        }
                    }
                    if ($td3 = $tdNodes->item(2)) {
                        $name = trim($td3->textContent);
                        if (isset($this->categories[$name])) {
                            $currentCategory = [$this->categories[$name]];
                        }
                        if (isset($this->categories[$name . ':'])) {
                            $currentCategory = [$this->categories[$name . ':']];
                        }
                    }
                    if ($url && $prodTitle && !$currentCategory) {
                        $this->markProgress('Category parsing failed: ' . $prodTitle . ' ' . $url);
                        exit;
                    }
                    if ($url && $prodTitle && $currentCategory) {
                        $releaseInfo = [];

                        if (!isset($prodsIndex[$prodId])) {
                            $prodsIndex[$prodId] = [
                                'id' => $prodId,
                                'title' => $prodTitle,
                                'directCategories' => $currentCategory,
                                'labels' => [],
                                'releases' => [],
                            ];
                        }
                        $releaseInfo['fileUrl'] = $this->rootUrl . $url;
                        $releaseInfo['id'] = md5(basename($url));
                        $releaseInfo['labels'] = [];

                        if ($td4 = $tdNodes->item(3)) {
                            $this->parseAuthorsString((string)$td4->nodeValue, $prodsIndex[$prodId]);
                        }
                        $roles = [];
                        if (isset($settings['release'])) {
                            if (isset($settings['release']['authorRoles'])) {
                                $roles = $settings['release']['authorRoles'];
                            }
                        }
                        $roles[] = 'release';
                        if ($td5 = $tdNodes->item(4)) {
                            $this->parseAuthorsString((string)$td5->nodeValue, $releaseInfo, $roles, 'publishers');
                        }
                        if (isset($settings['release'])) {
                            if (isset($settings['release']['language'])) {
                                $releaseInfo['language'] = $settings['release']['language'];
                            }
                            if (isset($settings['release']['releaseType'])) {
                                $releaseInfo['releaseType'] = $settings['release']['releaseType'];
                            }
                            if (isset($settings['release']['hardwareRequired'])) {
                                $releaseInfo['hardwareRequired'] = $settings['release']['hardwareRequired'];
                            }
                        }
                        if (isset($settings['prod'])) {
                            if (isset($settings['prod']['directCategories'])) {
                                $prodsIndex[$prodId]['directCategories'] = $settings['prod']['directCategories'];
                            }
                        }
                        $this->parseANode($aNode, $releaseInfo);
                        $prodsIndex[$prodId]['releases'][] = $releaseInfo;
                    }
                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseSystem($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $divNodes = $xPath->query(".//tr/td[@colspan='3']/div[@align='center']", $node);
        if ($divNodes->length === 1) {
            foreach ($divNodes as $divNode) {
                $currentCategory = [];
                if (isset($settings['prod']['directCategories'])) {
                    $currentCategory = $settings['prod']['directCategories'];
                }

                foreach ($divNode->childNodes as $childNode) {
                    if ($childNode->nodeType == XML_ELEMENT_NODE) {
                        if (strtolower($childNode->tagName) == 'p') {
                            $aNodes = $xPath->query(".//b/font/a", $childNode);
                            if ($aNodes->length > 0) {
                                $aNode = $aNodes->item(0);
                                $name = trim($aNode->textContent);
                                if (isset($this->categories[$name])) {
                                    $currentCategory = [$this->categories[$name]];
                                }
                            }
                        }
                        if ($currentCategory) {
                            $liNodes = $xPath->query(".//li[@class='padding']", $childNode);
                            if ($liNodes->length > 0) {
                                foreach ($liNodes as $liNode) {
                                    $aNode = false;
                                    $textNode = false;
                                    foreach ($liNode->childNodes as $contentNode) {
                                        if ($contentNode->nodeType == XML_ELEMENT_NODE) {
                                            if (strtolower($contentNode->tagName) == 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                                                $aNode = $contentNode;
                                            }
                                        }
                                        if ($aNode) {
                                            if (substr(trim($contentNode->textContent), 0, 2) == 'by') {
                                                $textNode = $contentNode;
                                            }
                                        }

                                        if ($aNode && $textNode) {
                                            $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                                            $aNode = false;
                                            $textNode = false;
                                        }
                                    }
                                }
                                $currentCategory = false;
                            }
                        }
                    }
                }
            }
        }
        $this->importProdsIndex($prodsIndex);
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseSbor($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $divNodes = $xPath->query(".//tr/td[@colspan='3']/div[@align='center']", $node);
        if ($divNodes->length === 1) {
            foreach ($divNodes as $divNode) {
                $currentCategory = [];
                if (isset($settings['prod']['directCategories'])) {
                    $currentCategory = $settings['prod']['directCategories'];
                }

                foreach ($divNode->childNodes as $childNode) {
                    if ($childNode->nodeType == XML_ELEMENT_NODE) {
                        if (strtolower($childNode->tagName) == 'p') {
                            $aNodes = $xPath->query(".//b/font", $childNode);
                            if ($aNodes->length > 0) {
                                $aNode = $aNodes->item(0);
                                $name = trim($aNode->textContent);
                                if (isset($this->categories[$name])) {
                                    $currentCategory = [$this->categories[$name]];
                                }
                                if (isset($this->categoryHardware[$name])) {
                                    $settings['release']['hardwareRequired'][] = $this->categoryHardware[$name];
                                }
                            }
                        }
                        if ($currentCategory) {
                            $liNodes = $xPath->query(".//li", $childNode);
                            if ($liNodes->length > 0) {
                                foreach ($liNodes as $liNode) {
                                    $aNode = false;
                                    $textNode = false;
                                    foreach ($liNode->childNodes as $contentNode) {
                                        if ($contentNode->nodeType == XML_ELEMENT_NODE) {
                                            if (strtolower($contentNode->tagName) == 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                                                $aNode = $contentNode;
                                            }
                                        }
                                        if ($aNode) {
                                            if (substr(trim($contentNode->textContent), 0, 2) == 'by') {
                                                $textNode = $contentNode;
                                            }
                                        }

                                        if ($aNode && $textNode) {
                                            $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                                            $aNode = false;
                                            $textNode = false;
                                        }
                                    }
                                }
                                $currentCategory = [];
                            }
                        }
                    }
                }
            }
        }
        $this->importProdsIndex($prodsIndex);
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseList($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $currentCategory = [];
        if (isset($settings['prod']['directCategories'])) {
            $currentCategory = $settings['prod']['directCategories'];
        }

        $liNodes = $xPath->query(".//li[@class='padding']", $node);
        if ($liNodes->length > 0) {
            foreach ($liNodes as $liNode) {
                $aNode = false;
                $textNode = false;
                foreach ($liNode->childNodes as $contentNode) {
                    if ($contentNode->nodeType == XML_ELEMENT_NODE) {
                        if (strtolower($contentNode->tagName) == 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                            $aNode = $contentNode;
                        }
                    }
                    if ($aNode) {
                        if (substr(trim($contentNode->textContent), 0, 2) == 'by') {
                            $textNode = $contentNode;
                        }
                    }

                    if ($aNode && $textNode) {
                        $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                        $aNode = false;
                        $textNode = false;
                    }
                }
            }
        }

        $this->importProdsIndex($prodsIndex);
    }

    private function parseListItemRelease($aNode, $textNode, $currentCategory, $settings, &$prodsIndex)
    {
        $releaseInfo = [
            'id' => null,
            'title' => null,
            'fileUrl' => null,
            'hardwareRequired' => [],
        ];
        $this->parseANode($aNode, $releaseInfo);
        $fileName = basename($releaseInfo['fileUrl']);
        $releaseInfo['id'] = md5($fileName);
        if ($releaseInfo['fileUrl'] && $releaseInfo['title']) {
            $prodTitle = $releaseInfo['title'];
            if (strtolower(substr($prodTitle, -4)) == 'demo') {
                $prodTitle = trim(mb_substr($prodTitle, 0, -4));
            }
            $prodId = md5($prodTitle);
            if (!isset($prodsIndex[$prodId])) {
                $prodsIndex[$prodId] = [
                    'id' => $prodId,
                    'title' => $prodTitle,
                    'labels' => [],
                    'directCategories' => $currentCategory,
                    'releases' => [],
                ];
            }

            $roles = [];
            if (isset($settings['release'])) {
                if (isset($settings['release']['authorRoles'])) {
                    $roles = $settings['release']['authorRoles'];
                }
            }
            $prodInfo = &$prodsIndex[$prodId];

            if (isset($settings['release'])) {
                if (!empty($settings['release']['language'])) {
                    $releaseInfo['language'] = $settings['release']['language'];
                }
                if (!empty($settings['release']['releaseType'])) {
                    $releaseInfo['releaseType'] = $settings['release']['releaseType'];
                }
                if (!empty($settings['release']['hardwareRequired'])) {
                    $releaseInfo['hardwareRequired'] = $settings['release']['hardwareRequired'];
                }
            }
            if (isset($settings['prod'])) {
                if (isset($settings['prod']['directCategories'])) {
                    $prodInfo['directCategories'] = $settings['prod']['directCategories'];
                }
            }
            $this->parseTextNode($textNode, $prodInfo, $releaseInfo, $roles);

            $prodInfo['releases'][] = $releaseInfo;
        }
    }

    protected function parseTextNode(
        $node,
        &$prodInfo,
        &$releaseInfo,
        $roles = []
    )
    {
        $text = trim($node->textContent);
        if (strtolower(substr($text, 0, 3)) == 'by ') {
            $text = substr($text, 3);
        }
        if (str_contains($text, ' - ')) {
            $strings = explode(' - ', $text);
            $this->parseAuthorsString($strings[0], $prodInfo, $roles);
            $this->parseDescription($strings[1], $prodInfo, $releaseInfo);
        } else {
            $this->parseAuthorsString($text, $prodInfo, $roles);
        }

    }

    protected function parseDescription($text, &$prodInfo, &$releaseInfo)
    {
        foreach ($this->hwIndex as $key => $value) {
            if (stripos($text, $key) !== false) {
                if (str_contains($key, '(')) {
                    $text = str_ireplace($key, '', $text);
                }

                $releaseInfo['hardwareRequired'][] = $value;
            }
        }
        $text = trim($text);
        if ($text) {
            $prodInfo['description'] = $text;
        }
    }

    protected function parseANode(
        $node,
        &$releaseInfo
    )
    {
        $releaseInfo['fileUrl'] = $this->rootUrl . $node->getAttribute('href');
        $text = $node->textContent;

        foreach ($this->hwIndex as $key => $value) {
            if (stripos($text, $key) !== false) {
                if (str_contains($key, '(')) {
                    $text = str_ireplace($key, '', $text);
                }

                $releaseInfo['hardwareRequired'][] = $value;
            }
        }
        if ((stripos($text, '(dsk)')) !== false) {
            $text = str_ireplace('(dsk)', '', $text);
            $releaseInfo['releaseType'] = 'adaptation';
        }
        if ((stripos($text, '(mod)')) !== false) {
            $text = str_ireplace('(mod)', '', $text);
            $releaseInfo['releaseType'] = 'mod';
        }
        if ((stripos($text, '(rus)')) !== false) {
            $text = str_ireplace('(rus)', '', $text);
            $releaseInfo['language'] = ['ru'];
        }
        if ((stripos($text, '(ita)')) !== false) {
            $text = str_ireplace('(ita)', '', $text);
            $releaseInfo['language'] = ['it'];
        }
        if ((stripos($text, '(pol)')) !== false) {
            $text = str_ireplace('(pol)', '', $text);
            $releaseInfo['language'] = ['pl'];
        }
        if ((stripos($text, '(eng)')) !== false) {
            $text = str_ireplace('(eng)', '', $text);
            $releaseInfo['language'] = ['en'];
        }
        if (preg_match('#(v[0-9]\.[0-9])#i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];
            $versionString = substr($text, $offset + 1);
            $text = trim(substr_replace($text, '', $offset));
            $releaseInfo['version'] = $versionString;
        }
        $releaseInfo['title'] = $this->processTitle($text);
    }

    private function processTitle($text)
    {
        //remove (..)
        $text = preg_replace('#([(].*[)])*#', '', $text);
        //remove double spaces
        $text = trim(
            preg_replace('!\s+!', ' ', $text),
            " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0)
        );
        if (strtolower(substr($text, -4)) == 'demo') {
            $text = trim(mb_substr($text, 0, -4));
        }
        return $text;
    }

    protected function parseAuthorsString(
        $string,
        &$info,
        $roles = [],
        $groupField = 'groups'
    )
    {
        $string = trim(preg_replace('!\s+!', ' ', $string), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
        if ($string === 'author') {
            $info['releaseType'] = 'original';
        }
        if ($string !== 'n/a' && $string !== 'author') {
            $parts = explode(',', $string);
            foreach ($parts as $part) {
                if (preg_match("#'([0-9]+)#", $part, $matches)) {
                    if (isset($matches[1])) {
                        $matches[1] = trim($matches[1]);
                        if (strlen($matches[1]) == 2) {
                            if ($matches[1] > 50) {
                                $info['year'] = '19' . $matches[1];
                            } else {
                                $info['year'] = '20' . $matches[1];
                            }
                        }
                    }
                    if (strlen($matches[1]) == 4) {
                        $info['year'] = $matches[1];
                    }
                    $name = trim(preg_replace("#('[0-9]+)#", '', $part));
                } else {
                    $name = trim($part);
                }
                if (stripos($name, '/') !== false) {
                    $parts = explode('/', $name);
                    $name = trim($parts[0]);
                    if ($groupName = trim($parts[1])) {
                        $groupLabel = [
                            'id' => $groupName,
                            'title' => $groupName,
                            'countryId' => null,
                            'isGroup' => true,
                            'isPerson' => null,
                            'isAlias' => null,
                        ];
                        $info['labels'][] = $groupLabel;
                        $info[$groupField][] = $groupLabel['id'];
                    }
                }
                $found = false;
                foreach ($info['labels'] as $labelInfo) {
                    if ($labelInfo['title'] == $name) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $info['labels'][] = [
                        'id' => $name,
                        'title' => $name,
                        'countryId' => null,
                        'isGroup' => null,
                        'isPerson' => null,
                        'isAlias' => null,
                    ];
                }
            }
            if (count($info['labels']) > 1) {
                $last = last($info['labels']);
                if (!empty($info['groups'])) {
                    if (!in_array($last['id'], $info['groups'])) {
                        $info['publishers'][] = $last['id'];
                    }
                }
            }

            $ids = array_column($info['labels'], 'id');
            foreach ($ids as $id) {
                $info['undetermined'][$id] = $roles;
            }
        }
    }

    protected function loadHtml(
        $url
    )
    {
        if ($contents = file_get_contents($url)) {
            $dom = new DOMDocument;
            $dom->encoding = 'UTF-8';
            $dom->recover = true;
            $dom->substituteEntities = true;
            $dom->strictErrorChecking = false;
            $dom->formatOutput = false;
            @$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . $contents);
            $dom->normalizeDocument();
            return $dom;
        }
        return false;
    }

    protected function markProgress(
        $text
    )
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo $text . ' ' . sprintf("%.2f", $endTime - $previousTime) . '<br/>';
        flush();
        file_put_contents(ROOT_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }

    /**
     * @param array $prodsIndex
     * @return void
     */
    protected function importProdsIndex(array $prodsIndex): void
    {
        foreach ($prodsIndex as $key => $prodInfo) {
            $this->counter++;
            if ($this->counter > $this->maxCounter) {
                exit;
            }
            if ($this->prodsManager->importProd($prodInfo, $this->origin)) {
                $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodInfo['title']);
            } else {
                $this->markProgress('prod failed ' . $prodInfo['title']);
            }
        }
    }
}