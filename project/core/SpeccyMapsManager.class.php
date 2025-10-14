<?php

use Illuminate\Database\Connection;
use ZxArt\Prods\Services\ProdsService;

/**
 * todo: re-implement import operations
 */
class SpeccyMapsManager extends errorLogger
{
    protected $urls = [
//        'https://maps.speccy.cz/index.php?sort=4&part=0&ath=0&wosid=0000000',
        'https://maps.speccy.cz/index.php?sort=4&part=99&ath=0&wosid=0000000',
    ];
    /**
     * @var ProdsService
     */
    protected $prodsService;
    protected $origin = 'maps';
    protected $rootUrl = 'https://maps.speccy.cz/';
    protected $prodsIndex;
    private $counter = 0;
    protected Connection $db;

    /**
     * @param mixed $prodsService
     */
    public function setProdsService(ProdsService $prodsService): void
    {
        $this->prodsService = $prodsService;
        $this->prodsService->setUpdateExistingProds(true);
        $this->prodsService->setAddImages(true);
    }

    public function setDb(Connection $db): void
    {
        $this->db = $db;
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
            $tableNodes = $xPath->query('//table[@class="t1"]');
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
        $prodNodes = $xPath->query(".//td[@width='43%']/a", $node);
        if ($prodNodes->length > 0) {
            foreach ($prodNodes as $prodNode) {
                $this->counter++;
                $mapUrl = $this->rootUrl . $prodNode->getAttribute('href');
                $info = parse_url($mapUrl);
                parse_str($info['query'], $output);
                $id = $output['id'];
                if (!$this->mapExists($id)) {
                    $this->downloadProd($mapUrl, $id);
//                if ($this->counter >= 10) {
//                    break;
//                }
                }
            }
            foreach ($this->prodsIndex as $key => $prodInfo2) {
                if ($prodElement = $this->prodsService->importProdOld($prodInfo2, $this->origin)) {
                    $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $prodElement->title);
                } else {
                    $this->markProgress('prod failed ' . $prodInfo2['id']);
                }
            }
            $this->markProgress('end of prods index');
        }
    }

    private function mapExists($id)
    {
        return $this->db->table('import_origin')->where('importId', '=', $id)->first();
    }

    private function downloadProd(string $pageUrl, $mapsId): void
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $zxDbId = null;
            $mapsAuthor = null;
            if ($imgNodes = $xPath->query(".//img[@id='obrazek']")) {
                if ($imgNode = $imgNodes->item(0)) {
                    $mapsUrl = $this->rootUrl . trim($imgNode->getAttribute('src'));
                }
            }
            if ($aNodes = $xPath->query(".//a")) {
                foreach ($aNodes as $aNode) {
                    if (trim($aNode->textContent) === 'SC') {
                        $scLink = $aNode->getAttribute('href');
                        $info = parse_url($scLink);
                        parse_str($info['query'], $output);
                        $zxDbId = (int)$output['id'];
                    }
                }
            }
            if ($tdNodes = $xPath->query(".//td[@class='t2']")) {
                foreach ($tdNodes as $tdNode) {
                    foreach ($tdNode->childNodes as $i => $childNode) {
                        if (trim($childNode->textContent) === 'Author:') {
                            $mapsAuthor = trim($childNode->nextSibling->textContent);
                        }
                    }
                }
            }


            if ($zxDbId && $mapsUrl && $mapsId) {
                $this->prodsIndex[$zxDbId] = [
                    'id' => $mapsId,
                    'title' => '',
                    'labels' => [],
                    'images' => [],
                    'directCategories' => [],
                    'releases' => [],
                    'party' => [],
                    'maps' => [
                        [
                            'url' => $mapsUrl,
                            'author' => $mapsAuthor,
                        ],
                    ],
                    'ids' => ['zxdb' => $zxDbId],
                ];
            }
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