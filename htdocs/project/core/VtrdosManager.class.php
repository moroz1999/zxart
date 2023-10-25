<?php

class VtrdosManager extends errorLogger
{
    protected $counter = 0;
    protected $maxCounter = 10;
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
    protected $categories = [
        'Архиваторы:' => '244886',
        'Ассемблеры:' => '92552',
        'Буты:' => '204150',
        'Графические Редакторы:' => '244860',
        'Графические Утилиты:' => '92587',
        'Дебаггеры:' => '244863',
        'Дисковые утилиты:' => '244864',
        'Записные книжки:' => '244865',
        'Каталогизаторы:' => '92569',
        'Командеры:' => '92187',
        'Конвертеры Экранов:' => '244866',
        'Копир MS-DOS - TR-DOS:' => '244867',
        'Копировщики:' => '244869',
        'Копировщики Disk -> Tape:' => '244868',
        'Музыкальные Плейеры:' => '244870',
        'Музыкальные редакторы для AY:' => '244871',
        'Музыкальные редакторы для beeper:' => '244872',
        'Музыкальные Утилиты для работы с AY-звуком:' => '244873',
        'Музыкальные утилиты для работы с цифровым звуком:' => '244874',
        'Операционные системы:' => '244875',
        'Программаторы:' => '244876',
        'Просмотрщики графики:' => '244862',
        'Просмотрщики текстов:' => '244877',
        'Прошивки ПЗУ:' => '244878',
        'Работа с сетями:' => '202587',
        'Разное:' => '92590',
        'Редакторы звуков:' => '244881',
        'Редакторы игр:' => '202586',
        'Редакторы Шрифтов:' => '92573',
        'Системные тесты:' => '244882',
        'Спрайтовые Редакторы:' => '244883',
        'Текстовые редакторы:' => '244884',
        'Универсальные просмотрщики:' => '244885',
        'Упаковщики данных:' => '244887',
        'Упаковщики экранов:' => '244888',
        'Утилиты для защиты данных:' => '244890',
        'Утилиты для работы с текстом:' => '244889',
        'Утилиты для снятия защиты:' => '244891',
        'Цифровые музыкальные редакторы:' => '244892',
        'Языки программирования:' => '244893',
    ];

    public function __construct()
    {
        $this->urlsSettings['https://vtrd.in/games.php?t=translat'] = [
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'language' => ['ru'],
                    'releaseType' => 'localization',
                    'authorRoles' => ['localization', 'release'],
                ],
            ],
        ];
        $this->urlsSettings['https://vtrd.in/games.php?t=full_ver'] = [
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
            ],
        ];
        $this->urlsSettings['https://vtrd.in/games.php?t=demo_ver'] = [
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'releaseType' => 'demoversion',
                ],
            ],
        ];
        $this->urlsSettings['https://vtrd.in/system.php'] = [
            [],
            [
                'type' => 'list',
            ],
        ];
        $this->urlsSettings['https://vtrd.in/gs.php'] = [
            [],
            [
                'type' => 'list',
                'prod' => [
                    'directCategories' => [244894],
                ],
                'release' => [
                    'hardwareRequired' => ['gs'],
                ],
            ],
            [],
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'hardwareRequired' => ['gs'],
                ],
            ],
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'releaseType' => 'mod',
                    'hardwareRequired' => ['gs'],
                    'authorRoles' => ['sfx', 'release'],
                ],
            ],
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'releaseType' => 'mod',
                    'hardwareRequired' => ['gs'],
                    'authorRoles' => ['intro_code', 'release'],
                ],
            ],
        ];
        $this->urlsSettings['https://vtrd.in/games.php?t=remix'] = [
            [
                'type' => 'table',
                'prod' => [
                    'directCategories' => [92177],
                ],
                'release' => [
                    'releaseType' => 'mod',
                    'authorRoles' => ['adaptation', 'release'],
                ],
            ],
        ];
        foreach ($this->alphabet as $item) {
            $this->urlsSettings['https://vtrd.in/games.php?t=' . $item] = [
                [
                    'type' => 'table',
                    'prod' => [
                        'directCategories' => [92177],
                    ],
                    'release' => [
                        'releaseType' => 'adaptation',
                        'authorRoles' => ['adaptation', 'release'],
                    ],
                ],
            ];
        }
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
                    if ($settings['type'] == 'list') {
                        $this->parseList($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] == 'table') {
                        $this->parseTable($tableNode, $xPath, $settings);
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

                    if ($aNode = $td1->getElementsByTagName('a')->item(0)) {
                        $url = $aNode->getAttribute('href');
                        $prodTitle = $this->processTitle($aNode->textContent);
                        $prodId = md5($prodTitle);
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

    /**
     * @param $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    protected function parseList($node, $xPath, $settings)
    {
        $prodsIndex = [];
        $fontNodes = $xPath->query(".//font", $node);
        $currentCategory = [];
        if (isset($settings['prod']['directCategories'])) {
            $currentCategory = $settings['prod']['directCategories'];
        }
        foreach ($fontNodes as $fontNode) {
            $aNode = false;
            $textNode = false;
            foreach ($fontNode->childNodes as $contentNode) {
                $name = trim($contentNode->textContent);
                if (isset($this->categories[$name])) {
                    $currentCategory = [$this->categories[$name]];
                }
                if ($contentNode->nodeType == XML_ELEMENT_NODE) {
                    if (strtolower($contentNode->tagName) == 'a') {
                        $aNode = $contentNode;
                    }
                }
                if ($aNode) {
                    if (substr(trim($contentNode->textContent), 0, 2) == 'by') {
                        $textNode = $contentNode;
                    }
                }

                if ($aNode && $textNode) {
                    $releaseInfo = [
                        'id' => null,
                        'title' => null,
                        'fileUrl' => null,
                        'hardwareRequired' => [],
                    ];
                    $this->parseANode($aNode, $releaseInfo);
                    $releaseInfo['id'] = md5(basename($releaseInfo['fileUrl']));
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
                        $this->parseTextNode($textNode, $prodInfo, $roles);

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
                                $prodInfo['directCategories'] = $settings['prod']['directCategories'];
                            }
                        }
                        $prodInfo['releases'][] = $releaseInfo;
                    }
                    $aNode = false;
                    $textNode = false;
                }
            }
        }
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

    protected function parseTextNode(
        $node,
        &$prodInfo,
        $roles = []
    )
    {
        $text = trim($node->textContent);
        if (strtolower(substr($text, 0, 3)) == 'by ') {
            $text = substr($text, 3);
        }
        $this->parseAuthorsString($text, $prodInfo, $roles);
    }

    protected function parseANode(
        $node,
        &$releaseInfo
    )
    {
        $hwIndex = [
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
            'Scorpion ZS 256' => 'scorpion',
            'Byte' => 'byte',
            'ATM Turbo' => 'atm',
            'smuc' => 'smuc',
            'Covox' => 'covoxfb',
            'General Sound' => 'gs',
            'Cache' => 'Cache',
            'SounDrive' => 'soundrive',
            'Turbo Sound' => 'ts',
            'ZXM-MoonSound' => 'zxm',
            'AY' => 'ay',
            'DMA UltraSound Card' => 'dmausc',
            'Beeper' => 'beeper',
            'AY Mouse' => 'aymouse',
        ];
        $releaseInfo['fileUrl'] = $this->rootUrl . $node->getAttribute('href');
        $text = $node->textContent;

        foreach ($hwIndex as $key => $value) {
            if (stripos($text, $key) !== false) {
                $releaseInfo['hardwareRequired'][] = $value;
            }
        }
        if ((stripos($text, '(dsk)')) !== false) {
            $releaseInfo['releaseType'] = 'adaptation';
        }
        if ((stripos($text, '(mod)')) !== false) {
            $releaseInfo['releaseType'] = 'mod';
        }
        if ((stripos($text, '(rus)')) !== false) {
            $releaseInfo['language'] = ['ru'];
        }
        if ((stripos($text, '(ita)')) !== false) {
            $releaseInfo['language'] = ['it'];
        }
        if ((stripos($text, '(pol)')) !== false) {
            $releaseInfo['language'] = ['pl'];
        }
        if ((stripos($text, '(eng)')) !== false) {
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

    private function processTitle($text){

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
            $dom->strictErrorChecking = false;
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
}