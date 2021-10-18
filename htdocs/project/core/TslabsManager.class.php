<?php

class TslabsManager extends errorLogger
{
    protected $typeCategories = [
        'Game' => 92177,
        'Demo' => 204819,
        'Example' => 92526,
        'Utility' => 92183,
    ];
    protected $roles = [
        'Code' => 'code',
        'Graphics' => 'graphics',
        'Music' => 'music',
        'ASCII' => 'ascii',
        'Text' => 'text',
        'Idea' => 'concept',
        'Producer' => 'organizing',
        'Remake' => 'adaptation',
        'Sound FX' => 'sfx',
    ];
    protected $prodsIndex = [];
    protected $counter = 0;
    protected $urls = [
        'http://prods.tslabs.info/'
    ];
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
    protected $origin = 'tsl';
    protected $rootUrl = 'http://prods.tslabs.info/';

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
        $this->prodsManager->setUpdateExistingProds(true);
        $this->prodsManager->setForceUpdateYoutube(true);
        $this->prodsManager->setResizeImages(true);
    }

    public function importAll()
    {
        foreach ($this->urls as $key => $url) {
            $this->importUrlProds($url);
            $this->markProgress('Prods imported from ' . $key . '/' . count($this->urls) . ' ' . $url);
        }
    }

    public function importUrlProds($pageUrl)
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $tableNodes = $xPath->query("//div[@class='container']/div[@class='row']/div[@class='col-md-10']");
            foreach ($tableNodes as $tableNode) {
                $this->parseTable($tableNode, $xPath);
            }
        }
    }

    /**
     * @param $node
     * @param DOMXPath $xPath
     */
    protected function parseTable($node, $xPath)
    {
        $this->prodsIndex = [];
        $releaseNodes = $xPath->query("./div[@class='row']", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                $url = null;
                $prodId = null;
                $prodTitle = null;
                $xPath->query("./div[@class='row']", $node);
                if ($nodes = $xPath->query(".//h2", $releaseNode)) {
                    if ($node = $nodes->item(0)) {
                        $prodTitle = $this->sanitizeString($node->textContent);
                    }
                }
                if ($nodes = $xPath->query(".//a[@class='btn btn-primary btn-sm']", $releaseNode)) {
                    if ($node = $nodes->item(0)) {
                        $url = $this->rootUrl . $node->getAttribute('href');
                        $fileName = basename($url);
                        $prodId = md5($fileName);
                    }
                }

                if ($prodId && $prodTitle) {
                    $releaseInfo = [];
                    $releaseInfo['fileUrl'] = $url;
                    $releaseInfo['id'] = $prodId;
                    $releaseInfo['hardwareRequired'] = ['zxevolution', 'tsconf'];
                    $releaseInfo['releaseType'] = 'original';
                    $releaseInfo['labels'] = [];

                    $this->prodsIndex[$prodId] = [
                        'id' => $prodId,
                        'title' => '',
                        'labels' => [],
                        'images' => [],
                        'directCategories' => [],
                        'releases' => [],
                        'party' => [],
                    ];

                    $prodInfo = &$this->prodsIndex[$prodId];

                    if ($nodes = $xPath->query(".//img[@class='img-rounded thumbnail ']", $releaseNode)) {
                        if ($node = $nodes->item(0)) {
                            $prodInfo['images'][] = $this->rootUrl . '/' . $node->getAttribute('src');
                        }
                    }
                    if ($nodes = $xPath->query(".//span[contains(@class, 'pull-right label')]", $releaseNode)) {
                        if ($node = $nodes->item(0)) {
                            $type = $this->sanitizeString($node->textContent);
                            if ($type === 'PC Tool') {
                                continue;
                            }
                            if (isset($this->typeCategories[$type])) {
                                $prodInfo['directCategories'] = [$this->typeCategories[$type]];
                            } else {
                                die('category is missing: ' . $type);
                            }
                        }
                    }
                    if ($nodes = $xPath->query(".//a[@class='btn btn-warning btn-sm']", $releaseNode)) {
                        if ($node = $nodes->item(0)) {
                            $trackedUrl = $node->getAttribute('href');
                            parse_str(parse_url($trackedUrl, PHP_URL_QUERY), $result);
                            $prodInfo['youtubeId'] = $result['v'];
                        }
                    }
                    if ($nodes = $xPath->query(".//h5", $releaseNode)) {
                        if ($node = $nodes->item(0)) {
                            $groupTitle = $this->sanitizeString($node->textContent);
                            $groupTitle = substr($groupTitle, 3);
                            $label = [
                                'id' => $groupTitle,
                                'title' => $groupTitle,
                                'locationLabel' => '',
                                'isGroup' => true,
                                'isPerson' => null,
                                'isAlias' => null,
                            ];
                            $prodInfo['labels'][] = $label;
                            $prodInfo['groups'][] = $label['id'];
                        }
                    }
                    if ($nodes = $xPath->query(".//dl[@class='dl-horizontal']/dt | .//dl[@class='dl-horizontal']/dd", $releaseNode)) {
                        $roleString = null;
                        $labelString = null;
                        foreach ($nodes as $node) {
                            if (strtolower($node->tagName) === 'dt') {
                                $roleString = $this->sanitizeString($node->textContent);
                            } elseif (strtolower($node->tagName) === 'dd') {
                                $labelString = $this->sanitizeString($node->textContent);
                            }
                            if ($roleString && $labelString) {
                                if (!$this->roles[$roleString]) {
                                    die('role is missing: ' . $roleString);
                                }
                                if ($labels = explode(',', $labelString)) {
                                    foreach ($labels as $labelName) {
                                        $labelName = trim($labelName);
                                        $label = [
                                            'id' => $labelName,
                                            'title' => $labelName,
                                            'locationLabel' => '',
                                            'isGroup' => null,
                                            'isPerson' => true,
                                            'isAlias' => null,
                                        ];
                                        $prodInfo['labels'][] = $label;
                                        $prodInfo['authors'][$label['id']] = [$this->roles[$roleString]];
                                    }
                                }
                                $roleString = null;
                                $labelString = null;
                            }
                        }
                    }

                    $prodInfo['title'] = $prodTitle;
                    $releaseInfo['title'] = $prodTitle;

                    $this->prodsIndex[$prodId]['releases'][] = $releaseInfo;
                }
            }
            foreach ($this->prodsIndex as $key => $prodInfo2) {
                $this->counter++;
                if ($this->prodsManager->importProd($prodInfo2, $this->origin)) {
                    $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodInfo2['title']);
                } else {
                    $this->markProgress('prod failed ' . $prodInfo2['title']);
                }
//                if ($this->counter > 500) {
//                    exit;
//                }
            }
            $this->markProgress('end of prods index');
        }
    }

    protected function sanitizeString($string)
    {
        return trim(preg_replace('!\s+!', ' ', $string), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
    }

    protected function loadHtml($url)
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

    protected function markProgress($text)
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