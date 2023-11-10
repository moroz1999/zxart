<?php

class ZxPressManager extends errorLogger
{
    protected int $counter = 0;
    protected int $maxCounter = 1;
    protected ProdsManager $prodsManager;
    protected string $origin = 'zxp';
    protected string $rootUrl = 'https://zxpress.ru/';

    private $prodsIndex = [];

    public function __construct()
    {
    }

    public function setProdsManager(ProdsManager $prodsManager)
    {
        $this->prodsManager = $prodsManager;
    }

    public function importAll()
    {
        $this->parsePress();
        $this->import();
    }

    private function import()
    {
        $this->parsePress();
        $this->prodsManager->setMatchProdsWithoutYear(true);
//        $this->importProdsIndex($this->prodsIndex);
    }

    private function parsePress()
    {
        $pageUrl = $this->rootUrl . 'ezines.php';
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $trNodes = $xPath->query("//table/tr");
            foreach ($trNodes as $trNode) {
                $aNodes = $xPath->query(".//td[@class='catalog']/a", $trNode);
                if ($aNodes->length) {
                    $aNode = $aNodes->item(0);
                    $this->parseIssuesPage($aNode->getAttribute('href'));
                    break;
                }
            }
        }
    }

    private function parseIssuesPage($pageUrl)
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $divNodes = $xPath->query("//div[@style='padding-top: 6px']");

            $prodInfo = false;
            $prodTitle = '';
            $prodYear = '';
            $prodId = '';

            foreach ($divNodes as $divNode) {
                $subDivNodes = $xPath->query(".//div", $divNode);
                foreach ($subDivNodes as $subDivNode) {
                    if ($subDivNode->getAttribute('style') === 'font: 12pt Georgia; color: #800; text-align: center') {
                        $aNodes = $xPath->query(".//a", $subDivNode);
                        if ($aNodes->length > 0) {
                            $aNode = $aNodes->item(0);
                            $articleUrl = $aNode->getAttribute('href');
                            parse_str(parse_url($articleUrl, PHP_URL_QUERY), $result);
                            $fragment = parse_url($articleUrl, PHP_URL_FRAGMENT);
                            $prodId = trim($result['id']) . '#' . $fragment;
                            $prodTitle = $this->sanitize($aNode->textContent);
                            $prodYear = '';
                        }
                        $deepDivs = $xPath->query(".//div[@style='font: 10pt; color: #312C12']", $subDivNode);
                        if ($deepDivs->length > 0) {
                            $dateString = $this->sanitize($deepDivs->item(0)->textContent);
                            $pattern = '/\d{4}/'; // This pattern matches a four-digit number

                            preg_match($pattern, $dateString, $matches);

                            if (!empty($matches)) {
                                $prodYear = $matches[0];
                            }
                        }
                        if ($prodId && $prodTitle) {
                            $this->prodsIndex[$prodId] = [
                                'id' => $prodId,
                                'title' => $prodTitle,
                                'year' => $prodYear,
                                'articles' => [],
                            ];

                            $prodInfo = &$this->prodsIndex[$prodId];
                        }
                    }
                    if ($prodInfo && $subDivNode->getAttribute('style') === 'font: 13pt/14pt Times; text-align: left') {
                        $aNodes = $xPath->query(".//a", $subDivNode);
                        if ($aNodes->length) {
                            $aNode = $aNodes->item(0);
                            $articleUrl = $aNode->getAttribute('href');
                            $articleTitle = '';
                            $articleIntroduction = '';
                            foreach ($aNode->childNodes as $childNode) {
                                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                                    if (strtolower($childNode->tagName) === 'b') {
                                        $articleTitle = $this->sanitize($childNode->textContent);
                                    }
                                } else {
                                    $articleIntroduction = $this->sanitize($childNode->textContent);
                                    if (mb_substr($articleIntroduction, 0, 2) === '- ') {
                                        $articleIntroduction = mb_substr($articleIntroduction, 2);
                                    }
                                }
                            }
                            if ($articleUrl && $articleTitle) {
                                if ($articleContent = $this->parseArticleContent($articleUrl)) {
                                    $prodInfo['articles'][] = [
                                        'url' => $articleUrl,
                                        'title' => $articleTitle,
                                        'introduction' => $articleIntroduction,
                                        'content' => $articleContent,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

        }
    }

    private function parseArticleContent($pageUrl)
    {
        $innerHTML = '';
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $preNodes = $xPath->query("//pre[@id='text']");
            if ($preNodes->length > 0) {
                $preNode = $preNodes->item(0);
                foreach ($preNode->childNodes as $child) {
                    $innerHTML .= $html->saveHTML($child);
                }
            }
        }
        return $innerHTML;
    }

    private function sanitize($value)
    {
        $value = str_ireplace("\r", '', $value);
        $value = str_ireplace("\n", ' ', $value);
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
        file_put_contents(ROOT_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
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