<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;

class ZxaaaManager extends errorLogger
{
    protected $maxCounter = 20000;
    protected $pagesAmount = 425;
    protected $minId = 1;
//    protected $debugEntry = 1312;
    protected $debugEntry;
    protected $ignore = [7955, 11487, 11506, 11542];

    protected $typeCategories = [
        'demo' => 204819,
        'gift' => 92172,
        'cracktro' => 92171,
    ];
    protected $categoriesWords = [
        '(16b intro)' => 262450,
        '(32b intro)' => 262451,
        '(64b intro)' => 262452,
        '(128b intro)' => 262453,
        '(256b intro)' => 92169,
        '(512b intro)' => 92168,
        '(1k intro)' => 92167,
        '(1k procedural graphics)' => 92167,
        '(4k intro)' => 92166,
        '(4k procedural graphics)' => 92166,
        '(8k intro)' => 92165,
        '(16k intro)' => 92164,
        '(e-book)' => 92591,
    ];
    protected $hwWords = [
        '(NeoGS)' => 'ngs',
        '(Pentagon)' => 'pentagon128',
        '(Covox)' => 'covoxfb',
        '(ATM Turbo 2+)' => 'atm2',
        '(ATM Turbo)' => 'atm',
        '(Pentagon 1024SL v2.x)' => 'pentagon1024',
        '(ZX Evolution)' => 'zxevolution',
        '(Base Conf)' => 'baseconf',
        '(GS)' => 'gs',
        '(FM)' => 'tsfm',
        '(General Sound)' => 'gs',
        '(ZXM Phoenix)' => 'zxmphoenix',
        '(Scorpion)' => 'scorpion',
        '(SounDrive)' => 'soundrive',
        '(SAA)' => 'saa',
        '(moonsound)' => 'zxm',
    ];
    protected $hwSoftWords = [
        'Profi' => 'profi',
        '48k' => 'zx48',
        '128k' => 'zx128',
    ];
    protected $extWords = [
        'GS' => 'gs',
        'TS' => 'ts',
        'FM' => 'tsfm',
        'SAA' => 'saa',
        'MS' => 'zxm',
    ];
    protected $softCategoriesWords = [
        'Crack Intro' => 92171,
        'Intro' => 92163,
        'Magazine' => 92179,
        'Megademo' => 92160,
        'Mega demo' => 92160,
        'Dentro' => 92162,
        'Invitation' => 92173,
        'Pack' => 315121,
        'musicdisk' => 92175,
        'music collection' => 92175,
        'music disk' => 92175,
        'Music Bank' => 92175,
        'boot' => 204150,
        'Trackmo' => 92161,
        'Help' => 92181,
    ];

    protected $prodsIndex = [];
    protected $urls = [];
    protected $counter = 0;

    /**
     * @var ProdsService
     */
    protected $prodsManager;
    /**
     * @var AuthorsService
     */
    protected $authorsManager;
    /**
     * @var GroupsService
     */
    protected $groupsService;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    protected $origin = '3a';
    protected $rootUrl = 'https://zxaaa.net';

    public function __construct()
    {
        for ($i = 1; $i <= $this->pagesAmount; $i++) {
            $this->urls[] = $this->rootUrl . '/view_demos.php?np=' . $i;
        }
    }

    /**
     * @param AuthorsService $authorsManager
     */
    public function setAuthorsManager($authorsManager): void
    {
        $this->authorsManager = $authorsManager;
        $authorsManager->setForceUpdateCountry(false);
        $authorsManager->setForceUpdateCity(false);
    }

    /**
     * @param GroupsService $groupsService
     */
    public function setGroupsService($groupsService): void
    {
        $this->groupsService = $groupsService;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager): void
    {
        $this->countriesManager = $countriesManager;
    }

    /**
     * @param mixed $prodsManager
     */
    public function setProdsService(ProdsService $prodsManager): void
    {
        $this->prodsManager = $prodsManager;
    }

    public function importAll(): void
    {
        foreach ($this->urls as $key => $url) {
            $this->importUrlProds($url);
            $this->markProgress('Prods imported from ' . $key . '/' . count($this->urls) . ' ' . $url);
        }
    }

    public function importUrlProds($pageUrl): void
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $tableNodes = $xPath->query("//table[@id='vd']");
            foreach ($tableNodes as $tableNode) {
                $this->parseTable($tableNode, $xPath);
            }
        }
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     *
     * @return void
     */
    protected function parseTable(DOMNode|DOMNameSpaceNode $node, $xPath)
    {
        $this->prodsIndex = [];
        $releaseNodes = $xPath->query(".//tr", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                $tdNodes = $releaseNode->getElementsByTagName('td');
                if ($td1 = $tdNodes->item(0)) {
                    $url = false;
                    $releaseTitle = false;
                    $prodId = false;
                    $prodTitle = false;
                    $aElements = $td1->getElementsByTagName('a');
                    if ($aNode = $aElements->item(0)) {
                        $releaseTitle = $this->sanitizeString($aNode->textContent);
                        $prodTitle = $releaseTitle;
                    }
                    if ($a2Node = $aElements->item(1)) {
                        $trackedUrl = $a2Node->getAttribute('href');
                        parse_str(parse_url($trackedUrl, PHP_URL_QUERY), $result);
                        $prodId = (int)$result['id'];
                        $url = $result['f'];
                    }
                    if (!empty($this->debugEntry) && $prodId != $this->debugEntry) {
                        continue;
                    }
                    if (in_array($prodId, $this->ignore)) {
                        continue;
                    }
                    if ($prodId <= $this->minId) {
                        continue;
                    }

                    if ($prodId && $prodTitle) {
                        $releaseInfo = [];
                        $releaseInfo['fileUrl'] = $this->rootUrl . '/' . $url;
                        $releaseInfo['id'] = md5($prodId . '_' . basename($url));
                        $releaseInfo['hardwareRequired'] = [];
                        $releaseInfo['labels'] = [];

                        if (!isset($this->prodsIndex[$prodId])) {
                            $this->prodsIndex[$prodId] = [
                                'id' => $prodId,
                                'title' => '',
                                'labels' => [],
                                'images' => [],
                                'directCategories' => [],
                                'releases' => [],
                                'party' => [],
                            ];
                        }
                        $prodInfo = &$this->prodsIndex[$prodId];
                        foreach ($this->categoriesWords as $categoriesWord => $categoryId) {
                            if (stripos($prodTitle, $categoriesWord) !== false) {
                                $prodTitle = trim(str_ireplace($categoriesWord, '', $prodTitle));
                                $prodInfo['directCategories'] = [$categoryId];
                            }
                        }
                        foreach ($this->hwWords as $hwWord => $hwCode) {
                            if (stripos($prodTitle, $hwWord) !== false) {
                                $prodTitle = trim(str_ireplace($hwWord, '', $prodTitle));
                                $releaseInfo['hardwareRequired'][] = $hwCode;
                            }
                        }

                        if (empty($prodInfo['directCategories'])) {
                            foreach ($this->softCategoriesWords as $categoriesWord => $categoryId) {
                                if (stripos($prodTitle, $categoriesWord) !== false) {
                                    $prodInfo['directCategories'] = [$categoryId];
                                    break;
                                }
                            }
                        }
                        foreach ($this->hwSoftWords as $hwWord => $hwCode) {
                            if (stripos($prodTitle, $hwWord) !== false) {
                                $releaseInfo['hardwareRequired'][] = $hwCode;
                            }
                        }
                        if (preg_match('#(v[0-9]\.[0-9])#i', $prodTitle, $matches, PREG_OFFSET_CAPTURE)) {
                            $offset = $matches[0][1];
                            $versionString = substr($prodTitle, $offset + 1);
                            $prodTitle = trim(substr_replace($prodTitle, '', $offset));
                            $releaseInfo['version'] = $versionString;
                        }

                        if ($td2 = $tdNodes->item(1)) {
                            $aElements = $td2->getElementsByTagName('a');
                            foreach ($aElements as $aElement) {
                                $prodInfo['images'][] = $this->rootUrl . '/' . $aElement->getAttribute('href');
                            }
                        }

                        if ($td3 = $tdNodes->item(2)) {
                            $aElements = $td3->getElementsByTagName('a');
                            if ($aNode = $aElements->item(0)) {
                                $trackedUrl = $aNode->getAttribute('href');
                                parse_str(parse_url($trackedUrl, PHP_URL_QUERY), $result);
                                $prodInfo['youtubeId'] = $result['v'];
                            }
                        }

                        if ($td4 = $tdNodes->item(3)) {
                            $prodInfo['year'] = (int)$td4->textContent;
                        }
                        if ($td6 = $tdNodes->item(5)) {
                            $location = '';
                            if ($td7 = $tdNodes->item(6)) {
                                $aElements = $td7->getElementsByTagName('a');
                                if ($aNode = $aElements->item(0)) {
                                    $location = $this->sanitizeString($aNode->textContent);
                                }
                            }
                            if ($location == 'Lvov') {
                                $location = 'Lviv';
                            }
                            if ($location == 'Rostov-na-Donu') {
                                $location = 'Rostov-on-Don';
                            }
                            $aElements = $td6->getElementsByTagName('a');
                            foreach ($aElements as $aElement) {
                                $name = $this->sanitizeString($aElement->textContent);
                                if ($name !== '[?]' && $name !== '???') {
                                    $label = [
                                        'id' => $name,
                                        'title' => $name,
                                        'locationName' => $location,
                                        'isGroup' => null,
                                        'isPerson' => null,
                                        'isAlias' => null,
                                    ];
                                    $prodInfo['labels'][] = $label;
                                    $prodInfo['undetermined'][$label['id']] = [];
                                }
                            }
                        }
                        if ($td8 = $tdNodes->item(7)) {
                            $partyTitle = null;
                            $partyYear = null;
                            $partyPlace = null;
                            $aElements = $td8->getElementsByTagName('a');
                            if ($aNode = $aElements->item(0)) {
                                $partyTitle = $this->sanitizeString($aNode->textContent);
                            }
                            if ($aNode = $aElements->item(1)) {
                                $partyYear = (int)$this->sanitizeString($aNode->textContent);
                            }
                            if ($text = $this->sanitizeString($td8->textContent)) {
                                $partyPlace = (int)$text;
                            }
                            if ($partyYear && $partyTitle) {
                                $prodInfo['party'] = [
                                    'title' => $partyTitle,
                                    'year' => $partyYear,
                                    'place' => $partyPlace,
                                ];
                            }
                        }
                        if ($td10 = $tdNodes->item(9)) {
                            $aElements = $td10->getElementsByTagName('a');
                            foreach ($aElements as $aElement) {
                                $ext = $this->sanitizeString($aElement->textContent);
                                if (isset($this->extWords[$ext])) {
                                    $releaseInfo['hardwareRequired'][] = $this->extWords[$ext];
                                }
                            }
                        }
                        if (empty($prodInfo['directCategories'])) {
                            if ($td11 = $tdNodes->item(10)) {
                                $type = $this->sanitizeString($td11->textContent);
                                if (isset($this->typeCategories[$type])) {
                                    $prodInfo['directCategories'] = [$this->typeCategories[$type]];
                                }
                            }
                        }

                        $prodInfo['title'] = $prodTitle;
                        $releaseInfo['title'] = $releaseTitle;

                        $this->prodsIndex[$prodId]['releases'][] = $releaseInfo;
                    }
                }
            }
            foreach ($this->prodsIndex as $key => $prodInfo2) {
                $this->counter++;
                if ($this->counter > $this->maxCounter) {
                    $this->markProgress('max counter hit ' . $this->maxCounter);
                    exit;
                }

                if ($this->prodsManager->importProd($prodInfo2, $this->origin)) {
                    $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodInfo2['title']);
                } else {
                    $this->markProgress('prod failed ' . $prodInfo2['title']);
                }
            }
            $this->markProgress('end of prods index');
        }
    }

    protected function sanitizeString($string): string
    {
        return trim(preg_replace('!\s+!', ' ', $string), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
    }

    protected function loadHtml($url): DOMDocument|false
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

    protected function markProgress(string $text): void
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo $text . ' ' . sprintf("%.2f", $endTime - $previousTime) . '<br/>';
        flush();
        file_put_contents(PUBLIC_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }
}
