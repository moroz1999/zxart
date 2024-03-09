<?php

class S4eManager extends errorLogger
{
    protected int $counter = 0;
    protected int $maxCounter = 4000;
    protected ProdsManager $prodsManager;
    protected AuthorsManager $authorsManager;
    protected GroupsManager $groupsManager;
    protected CountriesManager $countriesManager;
    protected string $origin = 's4e';
    protected string $rootUrl = 'https://spectrum4ever.org/';

    private array $releasers = [];
    private array $studios = [];
    private array $releases = [];
    private array $compilations = [];
    private $typeNames = [
        'studio' => 'studio',
        'предприятие' => 'company',
        'фирма' => 'company',
        'ТОО' => 'company',
        'центр' => 'store',
        'магазин' => 'store',
        'м.п.' => 'store',
        ', мп' => 'store',
    ];

    public function __construct()
    {
    }

    public function setAuthorsManager(AuthorsManager $authorsManager)
    {
        $this->authorsManager = $authorsManager;
    }

    public function setGroupsManager(GroupsManager $groupsManager)
    {
        $this->groupsManager = $groupsManager;
    }

    public function setCountriesManager(CountriesManager $countriesManager)
    {
        $this->countriesManager = $countriesManager;
    }

    public function setProdsManager(ProdsManager $prodsManager)
    {
        $this->prodsManager = $prodsManager;
    }

    public function importAll()
    {
        $this->parseReleasers();
        $this->parseStudios();
        $this->parseReleases();
        $this->parseCompilations();
        $this->import();
    }

    private function import()
    {
        $prodsIndex = [];
        foreach ($this->releases as $release) {
            if (!isset($prodsIndex[$release['prodId']])) {
                $prodsIndex[$release['prodId']] = [
                    'id' => $release['prodId'],
                    'title' => $release['title'],
                    'directCategories' => [92177],
                    'releases' => [],
                ];
            }
            $prodsIndex[$release['prodId']]['releases'][] = $release;
        }
        $this->prodsManager->setMatchProdsWithoutYear(true);
        $this->importProdsIndex($prodsIndex);

        $this->prodsManager->setMatchProdsWithoutYear(false);
        $this->importProdsIndex($this->compilations);
    }

    private function parseCompilations()
    {
        foreach ($this->studios as $id => $studio) {
//            if ($id !== 9) continue;
            if ($html = $this->loadHtml($studio['studioPageUrl'])) {
                $xPath = new DOMXPath($html);
                $tableNodes = $xPath->query("//table");
                foreach ($tableNodes as $tableNode) {
                    $prodTitle = null;
                    $description = null;
                    $prodId = null;
                    $images = [];
                    $image = '';
                    $compilationItems = [];
                    $imgNodes = $xPath->query(".//img[@class='cover_img']", $tableNode);
                    if ($imgNodes->length > 0) {
                        $imgNode = $imgNodes->item(0);
                        $image = $imgNode->getAttribute('onclick');
                        $prodId = (int)$imgNode->getAttribute('idtp');
                        $image = str_ireplace("img('", '', $image);
                        $image = str_ireplace("')", '', $image);
                        $images[] = $image;
                    }
                    if (!$prodId) {
                        $aNodes = $xPath->query(".//a[@class='orange']", $tableNode);
                        if ($aNodes->length > 0) {
                            $aNode = $aNodes->item(0);
                            $url = $aNode->getAttribute('href');
                            parse_str(parse_url($url, PHP_URL_QUERY), $result);
                            $prodId = (int)$result['id'];
                        }
                    }
                    $bigNodes = $xPath->query(".//big", $tableNode);
                    if ($bigNodes->length > 0) {
                        $bigNode = $bigNodes->item(0);
                        $prodTitle = $this->sanitize($bigNode->textContent);
                    }
                    $spanNodes = $xPath->query(".//span[@class='grn']", $tableNode);
                    if ($spanNodes->length > 0) {
                        $spanNode = $spanNodes->item(0);
                        $description = $this->sanitize($spanNode->textContent);
                    }
                    $liNodes = $xPath->query(".//li", $tableNode);
                    foreach ($liNodes as $liNode) {
                        $aNodes = $xPath->query(".//a[@class='yel']", $liNode);
                        if ($aNodes->length > 0) {
                            $aNode = $aNodes->item(0);
                            $prodUrl = $aNode->getAttribute('href');
                            parse_str(parse_url($prodUrl, PHP_URL_QUERY), $result);
                            $releaseId = (int)$result['id'];
                            if (!empty($this->releases[$releaseId])) {
                                $compilationItems[] = $releaseId;
                                $this->releases[$releaseId]['labels'][] = $studio;
                                $this->releases[$releaseId]['publishers'] = [$studio['id']];
                            }
                        }
                    }
                    if ($prodId) {
                        $prodId = 'comp' . $prodId;
                        $prodInfo = [
                            'id' => $prodId,
                            'title' => $prodTitle,
                            'description' => $description,
                            'inlayImages' => $images,
                            'compilationItems' => $compilationItems,
                            'labels' => [$studio],
                            'directCategories' => [92177],
                            'publishers' => [$studio['id']]
                        ];
                        if (!empty($this->compilations[$prodId])) {
                            echo 'Compilation exists: ' . $prodTitle . ' ' . $prodId . ' ' . $studio['studioPageUrl'];
                            exit;
                        }
                        $this->compilations[$prodId] = $prodInfo;
                    }
                }
            }
        }
    }

    private function parseReleases()
    {
        $pageUrl = $this->rootUrl . 'fulltape.php?go=releases';
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $trNodes = $xPath->query("//tr");

            $releaseId = null;
            $fileUrl = null;
            $title = null;
            $description = '';
            $releasers = [];
            $releasersRoles = [];
            $images = [];

            foreach ($trNodes as $trNode) {
                $releaseRow = false;
                $imagesRow = false;

                $tdNodes = $xPath->query(".//td[@class='yel']", $trNode);
                if ($tdNodes->length > 0) {
                    $releaseRow = true;
                }
                foreach ($tdNodes as $tdNode) {
                    $releaseId = null;
                    $fileUrl = null;
                    $title = null;
                    $description = '';
                    $releasers = [];
                    $releasersRoles = [];
                    $images = [];

                    foreach ($tdNode->childNodes as $childNode) {
                        if ($childNode->nodeType === XML_ELEMENT_NODE) {
                            if (strtolower($childNode->tagName) === 'a' && $childNode->getAttribute('class') === 'yel') {
                                $fileUrl = $this->rootUrl . $childNode->getAttribute('href');
                                $title = $this->sanitize($childNode->textContent);
                                parse_str(parse_url($fileUrl, PHP_URL_QUERY), $result);
                                $releaseId = (int)$result['id'];
                            }
                            if (strtolower($childNode->tagName) === 'a' && $childNode->getAttribute('class') === 'grey') {
                                $authorUrl = $childNode->getAttribute('href');
                                parse_str(parse_url($authorUrl, PHP_URL_QUERY), $result);
                                $authorId = (int)$result['id'];
                                if (!empty($this->releasers[$authorId])) {
                                    $releasers[] = $this->releasers[$authorId];
                                    $releasersRoles[$authorId] = ['release'];
                                }
                            }
                            if (strtolower($childNode->tagName) === 'span' && $childNode->getAttribute('class') === 'red') {
                                if ($content = $this->sanitize($childNode->textContent)) {
                                    $description .= "<p>" . $content . "</p>";
                                }
                            }
                            if (strtolower($childNode->tagName) === 'span' && $childNode->getAttribute('class') === 'blue') {
                                $properties = $this->sanitize($childNode->textContent);
                                if ($properties !== '____') {
                                    $description .= "<ul>";
                                    if (str_contains($properties, 'T')) {
                                        $description .= "<li>Text copyrights</li>";
                                    }
                                    if (str_contains($properties, 'P')) {
                                        $description .= "<li>Modified loading screen</li>";
                                    }
                                    if (str_contains($properties, 'B')) {
                                        $description .= "<li>Non-standard loading border</li>";
                                    }
                                    if (str_contains($properties, 'U')) {
                                        $description .= "<li>Non-standard screen loading scheme</li>";
                                    }
                                    $description .= "</ul>";
                                }
                            }
                        }
                    }
                }
                if ($releaseRow) {
                    continue;
                }
                $tdNodes = $xPath->query(".//td[contains(@id,'scr')]", $trNode);
                if ($tdNodes->length > 0) {
                    $imagesRow = true;
                }

                $imgNodes = $xPath->query(".//td/img[@class='ssht']", $trNode);
                if ($imgNodes->length > 0) {
                    foreach ($imgNodes as $imgNode) {
                        $images[] = $this->rootUrl . $imgNode->getAttribute('src');
                    }
                }
                if ($releaseId && $imagesRow) {
                    $releaseInfo = [
                        'id' => $releaseId,
                        'prodId' => md5($title),
                        'title' => mb_convert_case($title, MB_CASE_TITLE, "UTF-8"),
                        'releaseType' => 'crack',
                        'images' => $images,
                        'description' => $description,
                        'labels' => $releasers,
                        'fileUrl' => $fileUrl,
                        'fileName' => $title . '.tap',
                        'undetermined' => $releasersRoles,
                    ];
                    $this->releases[$releaseId] = $releaseInfo;
                }
            }
        }
    }

    private function parseReleasers()
    {
        $pageUrl = $this->rootUrl . 'fulltape.php?go=authors';
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $trNodes = $xPath->query("//tr");
            foreach ($trNodes as $trNode) {
                $authorId = null;
                $authorName = null;
                $realName = null;
                $cityName = null;
                $countryName = null;
                $locationName = null;
                $tdNodes = $xPath->query(".//td", $trNode);
                if ($tdNodes->length === 5) {
                    $td1Node = $tdNodes->item(1);
                    $aNodes = $xPath->query(".//a", $td1Node);
                    if ($aNode = $aNodes->item(0)) {
                        $url = $aNode->getAttribute('href');
                        parse_str(parse_url($url, PHP_URL_QUERY), $result);
                        $authorId = (int)$result['id'];
                        $authorName = $this->sanitize($aNode->textContent);
                    }
                    if ($authorName === '- n/a -') {
                        continue;
                    }
                    $authorName = mb_convert_case($authorName, MB_CASE_TITLE, "UTF-8");
                    $td2Node = $tdNodes->item(2);
                    if ($td2Node) {
                        if ($realName = $this->sanitize($td2Node->textContent)) {
                            $strings = explode(' ', $realName);
                            if ($strings) {
                                $first = array_shift($strings);
                                $strings[] = $first;
                                $realName = implode(' ', $strings);
                            }
                        }
                    }
                    $td3Node = $tdNodes->item(3);
                    if ($td3Node) {
                        if ($locationString = $this->sanitize($td3Node->textContent)) {
                            if (str_contains($locationString, ',')) {
                                $strings = explode(',', $locationString);
                                $cityName = trim($strings[0]);
                                $countryName = trim($strings[1]);
                            } else {
                                $locationName = trim($locationString);
                            }
                        }
                    }
                    $label = [
                        'id' => $authorId,
                        'title' => $authorName,
                        'realName' => $realName,
                        'cityName' => $cityName,
                        'countryName' => $countryName,
                        'locationName' => $locationName,
                        'isGroup' => false,
                        'isPerson' => true,
                        'isAlias' => null,
                    ];
                    $this->releasers[$authorId] = $label;
                }
            }
        }
    }

    private function parseStudios()
    {
        $pageUrl = $this->rootUrl . 'fulltape.php?go=studios';
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $trNodes = $xPath->query("//tr");
            foreach ($trNodes as $trNode) {
                $studioId = null;
                $studioName = null;
                $studioType = null;
                $cityName = null;
                $countryName = null;
                $locationName = null;
                $tdNodes = $xPath->query(".//td", $trNode);
                if ($tdNodes->length === 4) {
                    $td1Node = $tdNodes->item(1);
                    $aNodes = $xPath->query(".//a", $td1Node);
                    if ($aNode = $aNodes->item(0)) {
                        $url = $this->rootUrl . $aNode->getAttribute('href');
                        parse_str(parse_url($url, PHP_URL_QUERY), $result);
                        $studioId = (int)$result['id'];
                        $studioName = $this->sanitize($aNode->textContent);
                    }
                    if ($studioName === '- n/a -') {
                        continue;
                    }
                    $studioType = 'studio';
                    if (str_contains($studioName, ',')) {
                        $strings = explode(',', $studioName);
                        $studioName = trim($strings[1]) . " «" . trim($strings[0]) . "»";
                    }
                    foreach ($this->typeNames as $name => $type) {
                        if (str_contains(mb_strtolower($studioName), $name)) {
                            $studioType = $type;
                            break;
                        }
                    }

                    $studioName = mb_convert_case($studioName, MB_CASE_TITLE, "UTF-8");
                    $td2Node = $tdNodes->item(2);
                    if ($td2Node) {
                        if ($locationString = $this->sanitize($td2Node->textContent)) {
                            if (str_contains($locationString, ',')) {
                                $strings = explode(',', $locationString);
                                $cityName = trim($strings[0]);
                                $countryName = trim($strings[1]);
                            } else {
                                $locationName = trim($locationString);
                            }
                        }

                    }
                    $label = [
                        'id' => $studioId,
                        'title' => $studioName,
                        'cityName' => $cityName,
                        'countryName' => $countryName,
                        'locationName' => $locationName,
                        'studioPageUrl' => $url,
                        'type' => $studioType,
                        'isGroup' => true,
                        'isPerson' => false,
                        'isAlias' => null,
                    ];
                    $this->studios[$studioId] = $label;
                }
            }
        }
    }

    private function sanitize($value)
    {
        $value = str_ireplace("\r", '', $value);
        $value = str_ireplace("\n", '', $value);
        return trim($value);
    }

    private function loadHtml(
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

            $this->markProgress('html loaded ' . $url);

            return $dom;
        }
        return false;
    }

    private function markProgress(
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
        file_put_contents(PUBLIC_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }

    /**
     * @param array $prodsIndex
     * @return void
     */
    private function importProdsIndex(array $prodsIndex): void
    {
        foreach ($prodsIndex as $key => $prodInfo) {
            $this->counter++;
            if ($this->counter > $this->maxCounter) {
                return;
            }
            if ($this->prodsManager->importProd($prodInfo, $this->origin)) {
                $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodInfo['title']);
            } else {
                $this->markProgress('prod failed ' . $prodInfo['title']);
            }
        }
    }
}