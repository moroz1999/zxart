<?php

use ZxArt\Prods\Services\ProdsService;

class RzxArchiveManager extends errorLogger
{
    protected $urls = [
        'https://www.rzxarchive.co.uk/0.php',
        'https://www.rzxarchive.co.uk/a.php',
        'https://www.rzxarchive.co.uk/b.php',
        'https://www.rzxarchive.co.uk/c.php',
        'https://www.rzxarchive.co.uk/d.php',
        'https://www.rzxarchive.co.uk/e.php',
        'https://www.rzxarchive.co.uk/f.php',
        'https://www.rzxarchive.co.uk/g.php',
        'https://www.rzxarchive.co.uk/h.php',
        'https://www.rzxarchive.co.uk/i.php',
        'https://www.rzxarchive.co.uk/j.php',
        'https://www.rzxarchive.co.uk/k.php',
        'https://www.rzxarchive.co.uk/l.php',
        'https://www.rzxarchive.co.uk/m.php',
        'https://www.rzxarchive.co.uk/n.php',
        'https://www.rzxarchive.co.uk/o.php',
        'https://www.rzxarchive.co.uk/p.php',
        'https://www.rzxarchive.co.uk/q.php',
        'https://www.rzxarchive.co.uk/r.php',
        'https://www.rzxarchive.co.uk/s.php',
        'https://www.rzxarchive.co.uk/t.php',
        'https://www.rzxarchive.co.uk/u.php',
        'https://www.rzxarchive.co.uk/v.php',
        'https://www.rzxarchive.co.uk/w.php',
        'https://www.rzxarchive.co.uk/x.php',
        'https://www.rzxarchive.co.uk/y.php',
        'https://www.rzxarchive.co.uk/z.php',
    ];

    /**
     * @var ProdsService
     */
    protected $prodsManager;
    protected $origin = 'rzx';
    protected $rootUrl = 'https://www.rzxarchive.co.uk/';
    protected $prodsIndex;
    private $debugEntry;
    private $counter = 0;

    /**
     * @param mixed $prodsManager
     */
    public function setProdsService(ProdsService $prodsManager): void
    {
        $this->prodsManager = $prodsManager;
        $this->prodsManager->setUpdateExistingProds(true);
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
            $tableNodes = $xPath->query('//table[@width="100%"]');
            foreach ($tableNodes as $tableNode) {
                $this->parseTable($tableNode, $xPath);
            }
        }
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     */
    protected function parseTable(DOMNode|DOMNameSpaceNode $node, $xPath): void
    {
        $this->prodsIndex = [];
        $prodNodes = $xPath->query("./tr", $node);
        if ($prodNodes->length > 0) {
            foreach ($prodNodes as $prodNode) {
                $zxDbId = null;
                $rzxId = null;
                $rzxUrl = null;
                $rzxAuthor = null;
                if ($tdNodes = $xPath->query(".//td", $prodNode)) {
                    if ($tdNode = $tdNodes->item(1)) {
                        $rzxAuthor = trim($tdNode->textContent);
                    }
                }
                if ($aNodes = $xPath->query(".//a", $prodNode)) {
                    foreach ($aNodes as $aNode) {
                        if (trim($aNode->textContent) === 'Download') {
                            $rzxUrl = $this->rootUrl . $aNode->getAttribute('href');
                        }
                        if (trim($aNode->textContent) === 'Spectrum Computing page') {
                            $scLink = $aNode->getAttribute('href');
                            $info = parse_url($scLink);
                            parse_str($info['query'], $output);
                            $zxDbId = (int)$output['id'];
                        }
                        if (trim($aNode->getAttribute('title')) === 'link to here') {
                            $rzxPageUrl = $aNode->getAttribute('href');
                            $info = parse_url($rzxPageUrl);
                            $rzxId = $info['fragment'];
                        }
                    }
                }
                if ($this->debugEntry !== null && $zxDbId !== $this->debugEntry){
                    continue;
                }

                if ($zxDbId && $rzxUrl && $rzxId) {
                    $this->prodsIndex[$zxDbId] = [
                        'id' => $rzxId,
                        'title' => '',
                        'labels' => [],
                        'images' => [],
                        'directCategories' => [],
                        'releases' => [],
                        'party' => [],
                        'rzx' => [
                            [
                                'url' => $rzxUrl,
                                'author' => $rzxAuthor,
                            ],
                        ],
                        'ids' => ['zxdb' => $zxDbId],
                    ];
                }
            }
            foreach ($this->prodsIndex as $key => $prodInfo2) {
                $this->counter++;
                if ($prodElement = $this->prodsManager->importProd($prodInfo2, $this->origin)) {
                    $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodElement->title);
                } else {
                    $this->markProgress('prod failed ' . $prodInfo2['id']);
                }
//                if ($this->counter >= 10){
//                    exit;
//                }
            }
            $this->markProgress('end of prods index');
        }
    }

    protected function sanitizeString($string): string
    {
        return trim(preg_replace('!\s+!', ' ', $string), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
    }

    protected function attemptDownload(string $link): null|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Cache-Control: max-age=0',
        ]);

        $string = curl_exec($ch);
        curl_close($ch);

        return $string;
    }

    private function loadHtml(
        string $url,
    ): DOMDocument|false
    {
        if ($contents = $this->attemptDownload($url)) {
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